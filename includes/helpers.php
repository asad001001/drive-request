<?php
/**
 * Shared helpers: table name, security checks, sanitising, small HTML pieces.
 *
 * @package DriveRequest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Full name of the requests table. */
function dr_table() {
	global $wpdb;

	return $wpdb->prefix . 'drive_requests';
}

/** True for administrators. */
function dr_is_admin() {
	return current_user_can( 'manage_options' );
}

/** True for logged-in dealers and administrators. */
function dr_is_staff() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	return dr_is_admin() || in_array( 'dealer', (array) wp_get_current_user()->roles, true );
}

/**
 * Verify the AJAX nonce sent by assets/js/drive-request.js in the X-DR-Nonce header.
 * Ends the request with HTTP 403 when invalid.
 */
function dr_verify_nonce() {
	$nonce = isset( $_SERVER['HTTP_X_DR_NONCE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_DR_NONCE'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'drive_request' ) ) {
		wp_send_json_error( [ 'message' => 'Invalid or expired security token. Please reload the page.' ], 403 );
	}
}

/** Nonce + dealer/admin check for dashboard actions. */
function dr_require_staff() {
	dr_verify_nonce();

	if ( ! dr_is_staff() ) {
		wp_send_json_error( [ 'message' => 'You are not allowed to do this.' ], 403 );
	}
}

/**
 * Load a request row the current user is allowed to manage.
 * Admins can access any row; dealers only their own.
 *
 * @param mixed $raw_id Unsanitised ID from the request.
 * @return object Database row. Ends the request with 404 otherwise.
 */
function dr_get_owned_record( $raw_id ) {
	global $wpdb;

	$id     = absint( $raw_id );
	$record = $id ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . dr_table() . ' WHERE id = %d', $id ) ) : null;

	if ( ! $record || ( ! dr_is_admin() && (int) $record->dealer_id !== get_current_user_id() ) ) {
		wp_send_json_error( [ 'message' => 'Request not found.' ], 404 );
	}

	return $record;
}

/**
 * Pick allowed fields from $_POST and sanitise them for the requests table.
 *
 * @param string[] $allowed Column names to accept.
 * @return array
 */
function dr_posted_fields( array $allowed ) {
	$data = [];

	foreach ( $allowed as $field ) {
		if ( ! isset( $_POST[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- verified by caller.
			continue;
		}

		$value = wp_unslash( $_POST[ $field ] ); // phpcs:ignore WordPress.Security.NonceVerification

		switch ( $field ) {
			case 'email':
				$data[ $field ] = sanitize_email( $value );
				break;
			case 'dealer_id':
				$data[ $field ] = absint( $value );
				break;
			case 'rating':
				$data[ $field ] = min( 5, absint( $value ) );
				break;
			case 'date':
				$time           = strtotime( sanitize_text_field( $value ) );
				$data[ $field ] = $time ? gmdate( 'Y-m-d H:i:s', $time ) : current_time( 'mysql' );
				break;
			default:
				$data[ $field ] = trim( sanitize_text_field( $value ) );
		}
	}

	return $data;
}

/** Send the "request received" email to the customer. */
function dr_send_confirmation_email( $email ) {
	if ( is_email( $email ) ) {
		wp_mail( $email, 'Test Drive Request Submitted', 'Thank you for your interest in our products. Your test drive request has been received.' );
	}
}

/** Five-star rating markup. */
function dr_stars_html( $rating ) {
	$html = '';

	for ( $i = 1; $i <= 5; $i++ ) {
		$html .= $i <= (int) $rating ? "<span class='star-color'>&#9733;</span>" : '<span>&#9733;</span>';
	}

	return $html;
}

/** Human label for a status value. */
function dr_status_label( $status ) {
	$labels = [
		DR_STATUS_PENDING   => 'Pending',
		DR_STATUS_ACCEPTED  => 'Accepted',
		DR_STATUS_REJECTED  => 'Rejected',
		DR_STATUS_COMPLETED => 'Completed',
	];

	return $labels[ (int) $status ] ?? 'Unknown';
}

/** <option> list of every city saved on a dealer profile. */
function dr_city_options_html() {
	global $wpdb;

	$cities = $wpdb->get_col(
		"SELECT DISTINCT meta_value FROM {$wpdb->usermeta}
		 WHERE meta_key = 'city' AND meta_value <> ''
		 ORDER BY meta_value ASC"
	);

	$html = '';
	foreach ( $cities as $city ) {
		$html .= sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $city ), esc_html( $city ) );
	}

	return $html;
}

/** Message shown to visitors who must log in. */
function dr_login_prompt() {
	return sprintf( '<a href="%s">Please log in</a>', esc_url( wp_login_url( get_permalink() ) ) );
}
