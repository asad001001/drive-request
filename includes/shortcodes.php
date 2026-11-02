<?php
/**
 * Shortcodes.
 *
 *   [drive_request_form]      Public test-drive request form
 *   [getDealersByCity]        City → dealer picker with dealer contact card
 *   [createRequestByDealer]   Form for dealers to book a test drive
 *   [getPendingRequestDT]     Dealer/admin dashboard: pending requests
 *   [getAcceptedRequestDT]    Dealer/admin dashboard: accepted & completed
 *   [getRejectedRequestDT]    Dealer/admin dashboard: rejected requests
 *
 * @package DriveRequest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'drive_request_form', 'dr_shortcode_request_form' );
add_shortcode( 'getDealersByCity', 'dr_shortcode_dealers_by_city' );
add_shortcode( 'createRequestByDealer', 'dr_shortcode_dealer_request_form' );

foreach ( array_keys( dr_list_definitions() ) as $dr_list ) {
	add_shortcode( $dr_list, static function () use ( $dr_list ) {
		return dr_shortcode_list( $dr_list );
	} );
}
unset( $dr_list );

/** Gender radio buttons shared by both forms. */
function dr_gender_radios_html() {
	return "<div class='radiobtn'><input type='radio' id='gender_m' name='gender' value='Male' checked><label for='gender_m'>Male</label></div>
		<div class='radiobtn'><input type='radio' id='gender_f' name='gender' value='Female'><label for='gender_f'>Female</label></div>";
}

function dr_shortcode_request_form() {
	return sprintf(
		"<div id='save_alert'></div>
		<form id='drive_request'>
			<div class='field-set'>
				<input type='text' required name='first_name' id='first_name' placeholder='First Name'>
				<input type='text' required name='last_name' id='last_name' placeholder='Last Name'>
				<div class='req-radio-buttons'>%1\$s</div>
			</div>
			<div class='field-set'>
				<input type='tel' name='mobile' id='mobile' placeholder='Mobile'>
				<input type='email' name='email' id='email' placeholder='Email'>
				<input type='date' required name='date' value='%2\$s' min='%2\$s' id='date'>
			</div>
			<div class='field-set'>
				<select required name='city' id='cities'><option value=''>Select Your City</option>%3\$s</select>
				<select required name='dealer_id' id='dealers'><option value=''>Dealer</option></select>
			</div>
			<input type='submit' name='submit' value='Submit' id='submit'>
		</form>
		<div id='loading'></div>",
		dr_gender_radios_html(),
		esc_attr( current_time( 'Y-m-d' ) ),
		dr_city_options_html()
	);
}

function dr_shortcode_dealers_by_city() {
	return sprintf(
		"<div id='dealersBox'>
			<div class='cities_div col-6-b'>
				<select required name='city' id='cities'><option value=''>Select Your City</option>%s</select>
			</div>
			<div class='dealer_div col-6-b'>
				<select required name='dealer_id' id='dealers'><option value=''>Select Your Dealer</option></select>
			</div>
		</div>
		<div id='dealer_display_info'></div>",
		dr_city_options_html()
	);
}

function dr_shortcode_dealer_request_form() {
	if ( ! is_user_logged_in() ) {
		return dr_login_prompt();
	}

	if ( ! dr_is_staff() ) {
		return '<h4>Sorry! Only dealers can access this form.</h4>';
	}

	return sprintf(
		"<div id='save_alert'></div>
		<form id='DealerRequestForm'>
			<div class='field-set row'>
				<input type='text' required name='first_name' id='first_name' placeholder='First Name'>
				<input type='text' required name='last_name' id='last_name' placeholder='Last Name'>
				<div class='dealer-radio-buttons'>%1\$s</div>
			</div>
			<div class='field-set row'>
				<input type='tel' name='mobile' id='mobile' class='col-md-4' placeholder='Mobile'>
				<input type='email' name='email' id='email' class='col-md-4' placeholder='Email'>
				<input type='text' name='driving_licence' id='driving_licence' class='col-md-4' placeholder='Driving Licence'>
			</div>
			<div class='field-set row'>
				<select name='car_model' id='car_model'>%2\$s</select>
				<input type='date' required name='date' value='%3\$s' id='date'>
				<select name='purchase_type' id='purchase_type'>%4\$s</select>
				<select name='age' id='age'>%5\$s</select>
			</div>
			<div class='dealer-submit'><input type='submit' name='submit' value='Submit' id='submit'></div>
		</form>
		<div id='loading'></div>",
		dr_gender_radios_html(),
		dr_select_options( dr_car_models(), '', 'Select Car Model' ),
		esc_attr( current_time( 'Y-m-d' ) ),
		dr_select_options( dr_purchase_types(), '', 'Purchase Type' ),
		dr_select_options( dr_age_ranges(), '', 'Select Age' )
	);
}

/** Dashboard table shortcodes. */
function dr_shortcode_list( $list ) {
	if ( ! is_user_logged_in() ) {
		return dr_login_prompt();
	}

	if ( ! dr_is_staff() ) {
		return '<h4>Sorry! Only dealers can view this page.</h4>';
	}

	wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js', [ 'jquery' ], '1.13.8', true );
	wp_enqueue_script( 'datatables-bootstrap', 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap.min.js', [ 'datatables' ], '1.13.8', true );
	wp_enqueue_style( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', [], '3.3.7' );
	wp_enqueue_style( 'datatables-bootstrap', 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap.min.css', [ 'bootstrap' ], '1.13.8' );
	wp_enqueue_script( 'drive-request-tables', DR_URL . 'assets/js/datatables-init.js', [ 'drive-request', 'datatables' ], DR_VERSION, true );

	return dr_list_table_html( $list );
}
