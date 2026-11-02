<?php
/**
 * Plugin Name:       Drive Request
 * Description:       Test-drive booking for car dealerships: public request form, dealer dashboards (pending / accepted / rejected) and dealer-created requests.
 * Version:           3.0.0
 * Author:            Nand Lal Bheel, Asad Ali
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 *
 * @package DriveRequest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DR_VERSION', '3.0.0' );
define( 'DR_DB_VERSION', '1.0' );
define( 'DR_FILE', __FILE__ );
define( 'DR_DIR', plugin_dir_path( __FILE__ ) );
define( 'DR_URL', plugin_dir_url( __FILE__ ) );

/*
 * Request status values stored in {prefix}drive_requests.status.
 */
define( 'DR_STATUS_PENDING', 0 );
define( 'DR_STATUS_ACCEPTED', 1 );
define( 'DR_STATUS_REJECTED', 2 );
define( 'DR_STATUS_COMPLETED', 3 );

require_once DR_DIR . 'includes/helpers.php';
require_once DR_DIR . 'includes/class-drive-request.php';
require_once DR_DIR . 'includes/datatables.php';
require_once DR_DIR . 'includes/modals.php';
require_once DR_DIR . 'includes/shortcodes.php';
require_once DR_DIR . 'includes/user-fields.php';

register_activation_hook( __FILE__, [ 'Drive_Request', 'install' ] );

new Drive_Request();
