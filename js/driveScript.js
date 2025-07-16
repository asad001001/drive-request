jQuery(document).ready(function ($) {
  //select current date in test drive request




  $('#drive_request').submit(function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    $('#loading').html('<img src="' + ajaxLoadImage + '" alt="Smiley face" height="42" width="42">');
    $("#submit").attr("disabled", true);

    var formData = new FormData($(this)[0]);
    formData.append('action', 'saveDriveRequest');

    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      type: 'POST',
      success: function (data, textStatus, jqXHR) {
        $('#drive_request')[0].reset();
        $("#submit").attr("disabled", false);
        $('#loading').html('');
        if (data.msg) {
          swal({
            title: "Thanku",
            text: "Request Successfully Submited",
            icon: "success",
            button: "Close",
          });

        } else {
          alert('Sorry Record Not Saved')
        }
      },
      error: function (e) {
        console.log(e)
      }
    });
  });
  /* for accept drive Request */
  $(document).on('click', '.acceptRequest', function (e) {
    e.stopImmediatePropagation();
    var record_id = $(this).attr('record_id');
    $(this).closest('tr').remove();
    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: { action: 'requestAccepted', record_id: record_id },

      type: 'POST',
      success: function (data, textStatus, jqXHR) {
        var table = $('table').DataTable();
        table.ajax.reload()
      },
      error: function (e) {
        console.log(e)
      }
    });
  })
  /* for reject drive Request */



  $(document).on('click', '.editRequest', function (e) {
    event.preventDefault();

    var record_id = $(this).attr('record_id');
    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: { action: 'editRequest', record_id: record_id },
      type: 'POST',
      success: function (data, textStatus, jqXHR) {

        $('#popupModal').html(data);
        $('#editRequest').show();
      },
      error: function (e) {
        console.log(e)
      }
    });
  })

  //detail view model fetching 
  $(document).on('click', '.viewDetailsRequest', function (e) {

    event.preventDefault();
    var record_id = $(this).attr('record_id');
    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: { action: 'viewDetails', record_id: record_id },
      type: 'POST',
      success: function (data, textStatus, jqXHR) {

        $('#popupModal').html(data);
        $('#viewDetails').show()
      },
      error: function (e) {
        console.log(e)
      }
    });
  });

  $(document).on('submit', '#change_record_save', function (e) {

    event.preventDefault();
    e.stopImmediatePropagation();
    var formData = new FormData($(this)[0]);
    formData.append('action', 'saveRequestChanges');

    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      type: 'POST',
      success: function (data, textStatus, jqXHR) {

        if (data) {

          $('#change_record_save')[0].reset();
          $('#editRequest').hide();
          var table = $('table').DataTable();
          table.ajax.reload()

          if (data.msg) {
            swal({
              title: "Updated",
              text: "Record Updated Successfully",
              icon: "success",
              button: "Close",
            });


          } else {
            swal({
              title: "Sorry",
              text: "Something went Wrong",
              icon: "danger",
              button: "Close",
            });
          }

        } else {
          alert('Error');
        }
      },
      error: function (e) {
        console.log(e)
      }
    });
  });


  //Model Close
  $(document).on('click', '.close', function () {

    $(document).find('#editRequest').hide()
    $(document).find('#viewDetails').hide()
    $(document).find('#rejectRequest').hide()

  });
  $(document).on('click', '.cancel-btn', function () {

    $(document).find('#editRequest').hide()
    $(document).find('#viewDetails').hide()
    $(document).find('#rejectRequest').hide()

  });
  //mark as complete
  $(document).on('click', '.complete-btn', function (e) {
    record_id = $(this).attr('record_id')
    var formData = new FormData($('#change_record_save')[0]);
    formData.append('action', 'completedRequest');

    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: formData,
      processData: false,
      contentType: false,

      type: 'POST',
      success: function (data, textStatus, jqXHR) {

        $(document).find('#editRequest').hide()
        var table = $('table').DataTable();
        table.ajax.reload()
      },
      error: function (e) {
        console.log(e)
      }
    });
  })

  //select 2
  $(document).ready(function () {
    // $('#cities').select2();
  });
  //get all cityies
  $('#cities').change(function (e) {
    city = $(this).val();
    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: { action: 'getDealers', city: city },
      type: 'POST',
      success: function (data, textStatus, jqXHR) {

        $('#dealers').html(data);

      },
      error: function (e) {
        console.log(e)
      }
    });
  })
  $('#dealers').change(function (e) {
    user_id = $(this).val();
    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: { action: 'getUser', user_id: user_id },
      type: 'POST',
      success: function (data, textStatus, jqXHR) {
        $('#dealer_display_info').html(data);


      },
      error: function (e) {
        console.log(e)
      }
    });
  })
  $(document).on('change', '#reason_rejection', function (e) {
    var reason = $(this).val()
    if (reason == 'others') {
      text = "<textarea name='other_reason' required placeholder='Specify Reason' id ='other'></textarea>";
    } else {
      text = '';
    }
    $('#others').html(text);

  })

  //saveDealer Request 
  $('#DealerRequestForm').submit(function (e) {
    e.preventDefault();


    $('#loading').html('<img src="' + ajaxLoadImage + '" alt="Smiley face" height="42" width="42">');
    $("#submit").attr("disabled", true);


    var formData = new FormData($(this)[0]);
    formData.append('action', 'saveDealerRequest');
    $.ajax({
      url: ajaxDriveRequest.ajaxurl,
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      type: 'POST',
      success: function (data, textStatus, jqXHR) {
        $('#DealerRequestForm')[0].reset();
        $("#submit").attr("disabled", false);
        $('#loading').html('');
        if (data.msg) {
          swal({
            title: "Thanku",
            text: "Request Successfully Submited",
            icon: "success",
            button: "Close",
          });

        } else {
          alert('Sorry Record Not Saved')
        }
      },
      error: function (e) {
        console.log(e)
      }
    });
  })


});

