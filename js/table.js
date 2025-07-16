$ = jQuery;
$(document).ready(function() {
//accepted
$('#getAcceptedRequestDT').dataTable({
"processing": true,
"serverSide": true,
'iDisplayLength': 10,

"ajax": ajax_url,
"columnDefs": [
    { "orderable": false, "targets": [0,6] }
  ]
} );

//rejected data
$('#getRejectedRequestDT').dataTable({
    "processing": true,
    "serverSide": true,
    'iDisplayLength': 10,
    
    "ajax": ajax_url,
    "columnDefs": [
        { "orderable": false, "targets": 0}
      ]
    } );

// padding Requests 
$('#getPendingRequestDT').dataTable({
    "processing": true,
    "serverSide": true,
    'iDisplayLength': 10,
    
    "ajax": ajax_url,
    "columnDefs": [
        { "orderable": false, "targets":0 }
      ]
    } );
  
} );
