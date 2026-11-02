<?php
/**
 * Server-side DataTables endpoints for the pending / accepted / rejected dashboards.
 *
 * The three tables share one query builder; each list only defines its
 * statuses, extra column and row buttons.
 *
 * @package DriveRequest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * List definitions, keyed by the AJAX action / shortcode name.
 *
 * @return array
 */
function dr_list_definitions() {
	return [
		'getPendingRequestDT'  => [
			'statuses' => [ DR_STATUS_PENDING ],
			'extra'    => null,
			'buttons'  => static function ( $r ) {
				return sprintf(
					"<button record_id='%1\$d' class='acceptRequest'>Accept</button> | <button record_id='%1\$d' class='rejectRequest'>Reject</button>",
					$r->id
				);
			},
		],
		'getAcceptedRequestDT' => [
			'statuses' => [ DR_STATUS_ACCEPTED, DR_STATUS_COMPLETED ],
			'extra'    => [ 'Rating', 'rating', 'dr_stars_html' ],
			'buttons'  => static function ( $r ) {
				if ( DR_STATUS_ACCEPTED === (int) $r->status ) {
					return sprintf(
						"<button record_id='%1\$d' class='editRequest'>Edit</button> <button record_id='%1\$d' class='viewDetailsRequest'>View</button>",
						$r->id
					);
				}

				return sprintf( "<button record_id='%d' class='Completed'>Completed</button>", $r->id );
			},
		],
		'getRejectedRequestDT' => [
			'statuses' => [ DR_STATUS_REJECTED ],
			'extra'    => [ 'Rejection Reason', 'reason_rejection', 'esc_html' ],
			'buttons'  => static function ( $r ) {
				return sprintf( "<button record_id='%d' class='acceptRequest'>Accept</button>", $r->id );
			},
		],
	];
}

foreach ( array_keys( dr_list_definitions() ) as $dr_list ) {
	add_action( "wp_ajax_{$dr_list}", static function () use ( $dr_list ) {
		dr_datatable_response( $dr_list );
	} );
}
unset( $dr_list );

/**
 * Column keys shown in a list, in table order. Must match dr_list_table_html().
 */
function dr_list_columns( array $def ) {
	$columns = [ 'sr', 'first_name', 'last_name', 'mobile', 'email' ];

	if ( $def['extra'] ) {
		$columns[] = $def['extra'][1];
	}
	if ( dr_is_admin() ) {
		$columns[] = 'dealer_id';
	}
	$columns[] = 'action';

	return $columns;
}

/**
 * Handle a DataTables server-side request and print JSON.
 *
 * @param string $list Key from dr_list_definitions().
 */
function dr_datatable_response( $list ) {
	global $wpdb;

	dr_require_staff();

	$def     = dr_list_definitions()[ $list ];
	$columns = dr_list_columns( $def );
	$table   = dr_table();

	// phpcs:disable WordPress.Security.NonceVerification -- nonce checked in dr_require_staff().
	$draw   = absint( $_GET['draw'] ?? 0 );
	$start  = absint( $_GET['start'] ?? 0 );
	$length = min( 100, max( 1, absint( $_GET['length'] ?? 10 ) ) );
	$search = sanitize_text_field( wp_unslash( $_GET['search']['value'] ?? '' ) );

	// Only real, sortable columns can be used in ORDER BY.
	$order_index = absint( $_GET['order'][0]['column'] ?? 0 );
	$order_col   = $columns[ $order_index ] ?? 'sr';
	$order_col   = in_array( $order_col, [ 'sr', 'action' ], true ) ? 'id' : $order_col;
	$order_dir   = ( 'asc' === strtolower( $_GET['order'][0]['dir'] ?? '' ) && 'id' !== $order_col ) ? 'ASC' : 'DESC';
	// phpcs:enable

	// WHERE clause.
	$where  = [ 'status IN (' . implode( ',', array_fill( 0, count( $def['statuses'] ), '%d' ) ) . ')' ];
	$params = $def['statuses'];

	if ( ! dr_is_admin() ) {
		$where[]  = 'dealer_id = %d';
		$params[] = get_current_user_id();
	}

	if ( '' !== $search ) {
		$like     = '%' . $wpdb->esc_like( $search ) . '%';
		$where[]  = '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR mobile LIKE %s)';
		$params   = array_merge( $params, [ $like, $like, $like, $like ] );
	}

	$where_sql = implode( ' AND ', $where );

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table, column and direction are whitelisted above.
	$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE {$where_sql}", $params ) );
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$order_col} {$order_dir} LIMIT %d OFFSET %d",
			array_merge( $params, [ $length, $start ] )
		)
	);
	// phpcs:enable

	$data = [];
	foreach ( $rows as $i => $r ) {
		$row = [
			$start + $i + 1,
			esc_html( $r->first_name ),
			esc_html( $r->last_name ),
			esc_html( $r->mobile ),
			esc_html( $r->email ),
		];

		if ( $def['extra'] ) {
			$row[] = call_user_func( $def['extra'][2], $r->{$def['extra'][1]} );
		}

		if ( dr_is_admin() ) {
			$dealer = get_userdata( $r->dealer_id );
			$row[]  = $dealer ? esc_html( $dealer->first_name . ' ' . $dealer->last_name ) : '';
		}

		$row[]  = call_user_func( $def['buttons'], $r );
		$data[] = $row;
	}

	wp_send_json( [
		'draw'            => $draw,
		'recordsTotal'    => $total,
		'recordsFiltered' => $total,
		'data'            => $data,
	] );
}

/**
 * Markup for a dashboard table (used by the shortcodes).
 *
 * @param string $list Key from dr_list_definitions().
 */
function dr_list_table_html( $list ) {
	$def    = dr_list_definitions()[ $list ];
	$labels = [ 'Sr#', 'First Name', 'Last Name', 'Mobile#', 'Email' ];

	if ( $def['extra'] ) {
		$labels[] = $def['extra'][0];
	}
	if ( dr_is_admin() ) {
		$labels[] = 'Dealer';
	}
	$labels[] = 'Action';

	$head = '';
	foreach ( $labels as $label ) {
		$head .= '<th>' . esc_html( $label ) . '</th>';
	}

	// Modal containers are printed once per page, even with several tables.
	static $modals_printed = false;
	$modals         = $modals_printed ? '' : '<div id="popupModal"></div><div id="rejected_Model"></div>';
	$modals_printed = true;

	return sprintf(
		'%3$s<table id="%1$s" class="table table-striped table-hover dr-datatable" data-action="%1$s"><thead><tr>%2$s</tr></thead></table>',
		esc_attr( $list ),
		$head,
		$modals
	);
}
