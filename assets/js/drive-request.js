/**
 * Drive Request front-end: public form, dealer form, dashboard actions and modals.
 *
 * Every AJAX call carries the security nonce in the X-DR-Nonce header.
 */
jQuery(function ($) {
  'use strict';

  $.ajaxSetup({ headers: { 'X-DR-Nonce': DriveRequest.nonce } });

  /* ---------- helpers ---------- */

  function post(data, opts) {
    var isForm = data instanceof FormData;

    return $.ajax($.extend({
      url: DriveRequest.ajaxurl,
      type: 'POST',
      data: data,
      processData: !isForm,
      contentType: isForm ? false : 'application/x-www-form-urlencoded; charset=UTF-8'
    }, opts || {})).fail(function (xhr) {
      var msg = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) || 'Something went wrong.';
      swal({ title: 'Sorry', text: msg, icon: 'error', button: 'Close' });
    });
  }

  function formData($form, action) {
    var fd = new FormData($form[0]);
    fd.append('action', action);
    return fd;
  }

  function reloadTables() {
    $('.dr-datatable').each(function () {
      $(this).DataTable().ajax.reload(null, false);
    });
  }

  function closeModals() {
    $('#editRequest, #viewDetails, #rejectRequest').hide();
  }

  function showLoader(show) {
    $('#loading').html(show ? '<img src="' + DriveRequest.loader + '" alt="Loading" height="42" width="42">' : '');
    $('#submit').prop('disabled', show);
  }

  function submitRequestForm($form, action) {
    showLoader(true);

    post(formData($form, action)).done(function (res) {
      if (res && res.msg) {
        $form[0].reset();
        swal({ title: 'Thank you', text: 'Request submitted successfully.', icon: 'success', button: 'Close' });
      } else {
        swal({ title: 'Sorry', text: 'Your request could not be saved.', icon: 'error', button: 'Close' });
      }
    }).always(function () {
      showLoader(false);
    });
  }

  function openModal(action, recordId, modalId, container) {
    post({ action: action, record_id: recordId }, { dataType: 'html' }).done(function (html) {
      $(container).html(html);
      $(modalId).show();
    });
  }

  /* ---------- request forms ---------- */

  $('#drive_request').on('submit', function (e) {
    e.preventDefault();
    submitRequestForm($(this), 'saveDriveRequest');
  });

  $('#DealerRequestForm').on('submit', function (e) {
    e.preventDefault();
    submitRequestForm($(this), 'saveDealerRequest');
  });

  /* ---------- city → dealer pickers ---------- */

  $('#cities').on('change', function () {
    post({ action: 'getDealers', city: $(this).val() }).done(function (options) {
      $('#dealers').html(options);
      $('#dealer_display_info').empty();
    });
  });

  $('#dealers').on('change', function () {
    var userId = $(this).val();
    if (!userId) {
      $('#dealer_display_info').empty();
      return;
    }
    post({ action: 'getUser', user_id: userId }, { dataType: 'html' }).done(function (html) {
      $('#dealer_display_info').html(html);
    });
  });

  /* ---------- dashboard row actions ---------- */

  $(document).on('click', '.acceptRequest', function (e) {
    e.preventDefault();
    post({ action: 'requestAccepted', record_id: $(this).attr('record_id') }).done(reloadTables);
  });

  $(document).on('click', '.editRequest', function (e) {
    e.preventDefault();
    openModal('editRequest', $(this).attr('record_id'), '#editRequest', '#popupModal');
  });

  $(document).on('click', '.viewDetailsRequest', function (e) {
    e.preventDefault();
    openModal('viewDetails', $(this).attr('record_id'), '#viewDetails', '#popupModal');
  });

  $(document).on('click', '.rejectRequest', function (e) {
    e.preventDefault();
    openModal('requestRejected', $(this).attr('record_id'), '#rejectRequest', '#rejected_Model');
  });

  /* ---------- modal forms ---------- */

  $(document).on('submit', '#change_record_save', function (e) {
    e.preventDefault();

    post(formData($(this), 'saveRequestChanges')).done(function (res) {
      closeModals();
      reloadTables();
      swal(res && res.msg
        ? { title: 'Updated', text: 'Record updated successfully.', icon: 'success', button: 'Close' }
        : { title: 'Sorry', text: 'Something went wrong.', icon: 'error', button: 'Close' });
    });
  });

  $(document).on('click', '.complete-btn', function (e) {
    e.preventDefault();

    post(formData($('#change_record_save'), 'completedRequest')).done(function () {
      closeModals();
      reloadTables();
    });
  });

  $(document).on('change', '#reason_rejection', function () {
    $('#others').html($(this).val() === 'others'
      ? "<textarea name='other_reason' required placeholder='Specify reason' id='other'></textarea>"
      : '');
  });

  $(document).on('submit', '#rejection_form', function (e) {
    e.preventDefault();
    var $form = $(this);

    post({
      action: 'saveRejectionChanges',
      id: $form.find('input[name=id]').val(),
      reason: $form.find('#reason_rejection').val(),
      other: $form.find('#other').val() || ''
    }).done(function () {
      closeModals();
      reloadTables();
    });
  });

  $(document).on('click', '.close, .cancel-btn', closeModals);
});
