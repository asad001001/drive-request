/**
 * Initialise every dashboard table rendered by the [get*RequestDT] shortcodes.
 * The AJAX action comes from the table's data-action attribute.
 */
jQuery(function ($) {
  $('.dr-datatable').each(function () {
    var $table = $(this);

    $table.DataTable({
      processing: true,
      serverSide: true,
      pageLength: 10,
      order: [],
      ajax: {
        url: DriveRequest.ajaxurl,
        data: { action: $table.data('action') }
      },
      columnDefs: [
        { orderable: false, targets: [0, -1] }
      ]
    });
  });
});
