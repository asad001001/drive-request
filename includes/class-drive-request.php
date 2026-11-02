<?php
/**
 * Core: install, assets and AJAX handlers.
 *
 * @package DriveRequest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Drive_Request {

	/** Columns a customer may fill on the public form. */
	const PUBLIC_FIELDS = [ 'dealer_id', 'first_name', 'last_name', 'gender', 'city', 'email', 'mobile', 'date' ];

	/** Columns a dealer may fill when creating a request. */
	const DEALER_FIELDS = [ 'first_name', 'last_name', 'gender', 'email', 'mobile', 'driving_licence', 'car_model', 'purchase_type', 'age', 'date' ];

	/** Columns editable from the "Edit request" modal. */
	const EDIT_FIELDS = [ 'driving_licence', 'car_model', 'purchase_type', 'age', 'rating' ];

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Public (visitor) endpoints.
		foreach ( [ 'saveDriveRequest', 'getDealers', 'getUser' ] as $action ) {
			add_action( "wp_ajax_{$action}", [ $this, $action ] );
			add_action( "wp_ajax_nopriv_{$action}", [ $this, $action ] );
		}

		// Dealer / admin endpoints (logged-in only).
		foreach ( [ 'saveDealerRequest', 'requestAccepted', 'requestRejected', 'saveRejectionChanges', 'saveRequestChanges', 'completedRequest' ] as $action ) {
			add_action( "wp_ajax_{$action}", [ $this, $action ] );
		}
	}

	/**
	 * Activation: create the table and the dealer role.
	 */
	public static function install() {
		global $wpdb;

		$table           = dr_table();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			dealer_id INT(10) UNSIGNED NOT NULL,
			first_name VARCHAR(191) DEFAULT NULL,
			last_name VARCHAR(191) DEFAULT NULL,
			gender VARCHAR(191) DEFAULT NULL,
			age VARCHAR(191) DEFAULT NULL,
			city VARCHAR(191) DEFAULT NULL,
			email VARCHAR(100) DEFAULT NULL,
			mobile VARCHAR(15) DEFAULT NULL,
			driving_licence VARCHAR(191) DEFAULT NULL,
			car_model VARCHAR(191) DEFAULT NULL,
			purchase_type VARCHAR(191) DEFAULT NULL,
			rating TINYINT(3) UNSIGNED DEFAULT 0,
			status TINYINT(3) UNSIGNED DEFAULT 0,
			reason_rejection TINYTEXT DEFAULT NULL,
			date TIMESTAMP NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY dealer_status (dealer_id, status)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'drive_request_db_version', DR_DB_VERSION );
		add_role( 'dealer', 'Dealer', [ 'read' => true ] );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'drive-request', DR_URL . 'assets/css/style.css', [], DR_VERSION );

		wp_register_script( 'sweetalert', 'https://unpkg.com/sweetalert@2.1.2/dist/sweetalert.min.js', [], '2.1.2', true );
		wp_enqueue_script( 'drive-request', DR_URL . 'assets/js/drive-request.js', [ 'jquery', 'sweetalert' ], DR_VERSION, true );

		wp_localize_script( 'drive-request', 'DriveRequest', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'drive_request' ),
			'loader'  => DR_URL . 'assets/img/ajax-loader.gif',
		] );
	}

	/* ---------------------------------------------------------------------
	 * Public endpoints
	 * ------------------------------------------------------------------ */

	/** Customer submits the public test-drive form. */
	public function saveDriveRequest() {
		global $wpdb;

		dr_verify_nonce();

		$data = dr_posted_fields( self::PUBLIC_FIELDS );

		if ( empty( $data['first_name'] ) || empty( $data['dealer_id'] ) ) {
			wp_send_json( [ 'msg' => 0 ] );
		}

		$data['status'] = DR_STATUS_PENDING;

		$saved = $wpdb->insert( dr_table(), $data );

		if ( $saved && ! empty( $data['email'] ) ) {
			dr_send_confirmation_email( $data['email'] );
		}

		wp_send_json( [ 'msg' => $saved ? 1 : 0 ] );
	}

	/** Dealer <option>s for the chosen city. */
	public function getDealers() {
		dr_verify_nonce();

		$city  = isset( $_POST['city'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['city'] ) ) ) : '';
		$users = get_users( [
			'role'       => 'dealer',
			'meta_key'   => 'city', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value' => $city, // phpcs:ignore WordPress.DB.SlowDBQuery
		] );

		$options = "<option value=''>Select Your Dealer</option>";
		foreach ( $users as $user ) {
			$options .= sprintf( '<option value="%d">%s</option>', $user->ID, esc_html( $user->first_name . ' ' . $user->last_name ) );
		}

		wp_send_json( $options );
	}

	/** Public contact card for a dealer. */
	public function getUser() {
		dr_verify_nonce();

		$user = get_userdata( absint( $_POST['user_id'] ?? 0 ) );

		if ( ! $user || ! in_array( 'dealer', (array) $user->roles, true ) ) {
			wp_die();
		}

		$rows = [
			'City/Province' => $user->city . ' ' . $user->province,
			'Dealership'    => $user->first_name . ' ' . $user->last_name,
			'Phone Number'  => $user->phone1,
			'Address'       => $user->address,
		];

		foreach ( $rows as $label => $value ) {
			printf(
				"<div class='colmn'><span class='label_'>%s</span><span class='value'>%s</span></div>",
				esc_html( $label ),
				esc_html( $value )
			);
		}

		wp_die();
	}

	/* ---------------------------------------------------------------------
	 * Dealer / admin endpoints
	 * ------------------------------------------------------------------ */

	/** Dealer books a test drive directly (auto-accepted). */
	public function saveDealerRequest() {
		global $wpdb;

		dr_require_staff();

		$dealer_id = get_current_user_id();
		$data      = dr_posted_fields( self::DEALER_FIELDS );

		$data['dealer_id'] = $dealer_id;
		$data['city']      = (string) get_user_meta( $dealer_id, 'city', true );
		$data['status']    = DR_STATUS_ACCEPTED;

		$saved = $wpdb->insert( dr_table(), $data );

		if ( $saved && ! empty( $data['email'] ) ) {
			dr_send_confirmation_email( $data['email'] );
		}

		wp_send_json( [ 'msg' => $saved ? 1 : 0 ] );
	}

	public function requestAccepted() {
		global $wpdb;

		dr_require_staff();
		$record = dr_get_owned_record( $_POST['record_id'] ?? 0 );

		$updated = $wpdb->update( dr_table(), [ 'status' => DR_STATUS_ACCEPTED ], [ 'id' => $record->id ] );

		if ( $updated && is_email( $record->email ) ) {
			wp_mail( $record->email, 'Your Test Drive Request Was Accepted', 'Your dealer has accepted your test drive request and will contact you shortly.' );
		}

		wp_send_json( [ 'msg' => $updated ? 1 : 0 ] );
	}

	/** Returns the "reject request" modal. */
	public function requestRejected() {
		dr_require_staff();
		$record = dr_get_owned_record( $_POST['record_id'] ?? 0 );

		echo dr_reject_modal_html( $record ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
		wp_die();
	}

	public function saveRejectionChanges() {
		global $wpdb;

		dr_require_staff();
		$record = dr_get_owned_record( $_POST['id'] ?? 0 );

		$reason = sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) );
		if ( 'others' === $reason ) {
			$reason = sanitize_textarea_field( wp_unslash( $_POST['other'] ?? '' ) );
		}

		$updated = $wpdb->update(
			dr_table(),
			[
				'reason_rejection' => $reason,
				'status'           => DR_STATUS_REJECTED,
			],
			[ 'id' => $record->id ]
		);

		wp_send_json( [ 'msg' => $updated ? 1 : 0 ] );
	}

	public function saveRequestChanges() {
		global $wpdb;

		dr_require_staff();
		$record = dr_get_owned_record( $_POST['id'] ?? 0 );
		$data   = dr_posted_fields( self::EDIT_FIELDS );

		$updated = $data ? $wpdb->update( dr_table(), $data, [ 'id' => $record->id ] ) : false;

		wp_send_json( [ 'msg' => false !== $updated ? 1 : 0 ] );
	}

	public function completedRequest() {
		global $wpdb;

		dr_require_staff();
		$record = dr_get_owned_record( $_POST['id'] ?? 0 );

		$data           = dr_posted_fields( self::EDIT_FIELDS );
		$data['status'] = DR_STATUS_COMPLETED;

		$updated = $wpdb->update( dr_table(), $data, [ 'id' => $record->id ] );

		if ( $updated && is_email( $record->email ) ) {
			wp_mail( $record->email, 'Thank You for Your Test Drive', 'Your test drive has been marked as completed. Thank you for visiting us.' );
		}

		wp_send_json( [ 'msg' => $updated ? 1 : 0 ] );
	}
}
