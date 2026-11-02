<?php
/**
 * Modal endpoints and markup: edit, view details, reject.
 *
 * @package DriveRequest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_editRequest', 'dr_edit_request_modal' );
add_action( 'wp_ajax_viewDetails', 'dr_view_details_modal' );

/** Option values offered in the dropdowns. */
function dr_purchase_types() {
	return [ 'First Purchase', 'Exchange / Replacement', 'Additional Car' ];
}

function dr_age_ranges() {
	return [ '0-25', '26-35', '36-45', '45-Above' ];
}

function dr_car_models() {
	return [ 'Alto VX', 'Alto VXR', 'Alto VXL' ];
}

/**
 * <option> list with the current value selected (case/space-insensitive,
 * so older rows like "45-above" or "First Purches" still match).
 */
function dr_select_options( array $options, $current, $placeholder ) {
	$normalise = static function ( $v ) {
		return strtolower( preg_replace( '/[^a-z0-9]/i', '', (string) $v ) );
	};
	$aliases   = [ 'firstpurches' => 'firstpurchase' ]; // Legacy spelling stored by v2.
	$current   = $normalise( $current );
	$current   = $aliases[ $current ] ?? $current;

	$html = '<option value="">' . esc_html( $placeholder ) . '</option>';
	foreach ( $options as $option ) {
		$html .= sprintf(
			'<option value="%1$s"%2$s>%1$s</option>',
			esc_attr( $option ),
			$normalise( $option ) === $current ? ' selected' : ''
		);
	}

	return $html;
}

/** Shared modal wrapper. */
function dr_modal( $id, $title, $body ) {
	return sprintf(
		'<div id="%1$s" class="modal"><div class="modal-content">
			<div class="modal-head"><div class="edit-heading"><h4>%2$s</h4></div><span class="close">&times;</span></div>
			%3$s
		</div></div>',
		esc_attr( $id ),
		esc_html( $title ),
		$body
	);
}

function dr_edit_request_modal() {
	dr_require_staff();
	$r = dr_get_owned_record( $_POST['record_id'] ?? 0 );

	$rating = '';
	if ( ! $r->rating ) {
		$radios = '';
		for ( $i = 1; $i <= 5; $i++ ) {
			$radios .= sprintf(
				"<label class='radio'><input type='radio' name='rating' value='%1\$d' id='rating%1\$d' autocomplete='off'><span class='radio-label'>%1\$d</span></label>",
				$i
			);
		}
		$rating = "<div class='form-group row'><label class='col-form-label col-md-4'>Rating:</label><div class='btn-group btn-group-toggle col-md-8'>{$radios}</div></div>";
	}

	$body = sprintf(
		"<form id='change_record_save'>
			<input type='hidden' name='id' value='%1\$d'>
			<div class='form-group row'>
				<label for='driving_licence' class='col-form-label col-md-4'>Driving Licence:</label>
				<input type='text' name='driving_licence' class='col-md-8' value='%2\$s' id='driving_licence'>
			</div>
			<div class='form-group row'>
				<label for='car_model' class='col-form-label col-md-4'>Car Model:</label>
				<input type='text' name='car_model' class='col-md-8' value='%3\$s' id='car_model'>
			</div>
			<div class='form-group row'>
				<label for='purchase_type' class='col-form-label col-md-4'>Purchase Type:</label>
				<select name='purchase_type' id='purchase_type'>%4\$s</select>
			</div>
			<div class='form-group row'>
				<label for='age' class='col-form-label col-md-4'>Age:</label>
				<select name='age' id='age'>%5\$s</select>
			</div>
			%6\$s
			<div class='row modal_footer'>
				<div class='bottom-actions col-md-6'>
					<button type='submit' class='save-btn'>Save Changes</button>
					<button type='button' class='cancel-btn'>Cancel</button>
				</div>
				<div class='col-md-6 text-right'>
					<button type='button' record_id='%1\$d' class='complete-btn'>Mark as Complete</button>
				</div>
			</div>
		</form>",
		$r->id,
		esc_attr( $r->driving_licence ),
		esc_attr( $r->car_model ),
		dr_select_options( dr_purchase_types(), $r->purchase_type, 'Select Type' ),
		dr_select_options( dr_age_ranges(), $r->age, 'Select Age (Years)' ),
		$rating
	);

	echo dr_modal( 'editRequest', 'Edit Test Drive Request', $body ); // phpcs:ignore WordPress.Security.EscapeOutput
	wp_die();
}

function dr_view_details_modal() {
	dr_require_staff();
	$r = dr_get_owned_record( $_POST['record_id'] ?? 0 );

	$fields = [
		'Email'                 => esc_html( $r->email ),
		'Age'                   => esc_html( $r->age ),
		'Gender'                => esc_html( $r->gender ),
		'Mobile#'               => esc_html( $r->mobile ),
		'City'                  => esc_html( $r->city ),
		'Purchase Type'         => esc_html( $r->purchase_type ),
		'Car Model'             => esc_html( strtoupper( (string) $r->car_model ) ),
		'Licence'               => esc_html( strtoupper( (string) $r->driving_licence ) ),
		'Test Drive Experience' => dr_stars_html( $r->rating ),
		'Status'                => esc_html( dr_status_label( $r->status ) ),
		'Date'                  => $r->date ? esc_html( date_i18n( 'd-m-Y', strtotime( $r->date ) ) ) : '',
	];

	$body = '<div class="modal-body">';
	foreach ( $fields as $label => $value ) {
		$body .= sprintf( "<div class='row'><label class='col-sm-6'>%s</label><div class='col-sm-6'>%s</div></div>", esc_html( $label ), $value );
	}
	$body .= '</div>';

	echo dr_modal( 'viewDetails', $r->first_name . ' ' . $r->last_name, $body ); // phpcs:ignore WordPress.Security.EscapeOutput
	wp_die();
}

/** "Reject request" modal (returned by the requestRejected AJAX action). */
function dr_reject_modal_html( $r ) {
	$reasons = [ 'Wrong Information', 'Taken From Other Dealer', 'Postponed', 'Not interested' ];

	$options = "<option value=''>Select Reason of Reject</option>";
	foreach ( $reasons as $reason ) {
		$options .= sprintf( '<option value="%1$s">%1$s</option>', esc_attr( $reason ) );
	}
	$options .= "<option value='others'>Others (specify reason)</option>";

	$body = sprintf(
		"<form id='rejection_form'>
			<input type='hidden' name='id' value='%1\$d'>
			<div class='form-group row'>
				<label for='reason_rejection' class='col-form-label col-md-4'>Reason for Reject:</label>
				<select required name='reason_rejection' id='reason_rejection' class='col-md-8'>%2\$s</select>
			</div>
			<div id='others'></div>
			<div class='row modal_footer'>
				<div class='bottom-actions col-md-6'>
					<button type='submit' class='save-btn'>Save Changes</button>
					<button type='button' class='cancel-btn'>Cancel</button>
				</div>
			</div>
		</form>",
		$r->id,
		$options
	);

	return dr_modal( 'rejectRequest', 'Reject Drive Request', $body );
}
