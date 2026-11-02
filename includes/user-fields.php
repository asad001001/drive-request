<?php
/**
 * Extra dealer profile fields (address, phones, city, province) on the user screens.
 *
 * @package DriveRequest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Field key => label. */
function dr_user_fields() {
	return [
		'phone1'   => 'Phone 1',
		'phone2'   => 'Phone 2',
		'fax'      => 'Fax',
		'address'  => 'Address',
		'province' => 'Province',
		'city'     => 'City',
	];
}

function dr_provinces() {
	return [ 'Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan', 'Islamabad Capital Territory', 'Gilgit-Baltistan', 'Azad Jammu and Kashmir' ];
}

add_action( 'user_register', 'dr_save_user_fields' );
add_action( 'profile_update', 'dr_save_user_fields' );

function dr_save_user_fields( $user_id ) {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['dr_user_fields_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dr_user_fields_nonce'] ) ), 'dr_user_fields' ) ) {
		return;
	}

	foreach ( array_keys( dr_user_fields() ) as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$value = wp_unslash( $_POST[ $key ] );
		$value = 'address' === $key ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );

		if ( 'province' === $key && '' !== $value && ! in_array( $value, dr_provinces(), true ) ) {
			continue;
		}

		update_user_meta( $user_id, $key, trim( $value ) );
	}
}

add_action( 'show_user_profile', 'dr_render_user_fields' );
add_action( 'edit_user_profile', 'dr_render_user_fields' );
add_action( 'user_new_form', 'dr_render_user_fields' );

function dr_render_user_fields( $user ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$get = static function ( $key ) use ( $user ) {
		return is_object( $user ) ? (string) get_user_meta( $user->ID, $key, true ) : '';
	};
	?>
	<h3>Dealer details</h3>
	<?php wp_nonce_field( 'dr_user_fields', 'dr_user_fields_nonce' ); ?>
	<table class="form-table">
		<?php foreach ( dr_user_fields() as $key => $label ) : ?>
			<tr>
				<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
				<td>
					<?php if ( 'address' === $key ) : ?>
						<textarea name="address" id="address" class="regular-text" cols="30" rows="5"><?php echo esc_textarea( $get( 'address' ) ); ?></textarea>
					<?php elseif ( 'province' === $key ) : ?>
						<select name="province" id="province" class="regular-text">
							<option value="">Select Province</option>
							<?php foreach ( dr_provinces() as $province ) : ?>
								<option value="<?php echo esc_attr( $province ); ?>" <?php selected( $get( 'province' ), $province ); ?>><?php echo esc_html( $province ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" class="regular-text" value="<?php echo esc_attr( $get( $key ) ); ?>">
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php
}
