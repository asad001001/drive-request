

jQuery(document).ready(function($) {
    //get popup form for reject 
    $(document).on('click','.rejectRequest',function(e){
        e.stopImmediatePropagation();
        var record_id=$(this).attr('record_id');
       // $(this).closest ('tr').remove ();
        $.ajax({
        url         : ajax.ajaxurl,
        data        : {action:'requestRejected',record_id:record_id},
        type        : 'POST',
        success     : function(data, textStatus, jqXHR){
            $('#rejected_Model').html(data);
            $('#rejectRequest').show()
            
        },
        error:function(e){
            console.log(e)
        }
    });
    })

    $(document).on('submit','#rejection_form',function(e){
        e.stopImmediatePropagation();
        e.preventDefault()
       
       // var data = new FormData($(this));
      var reason_rejection= $("#reason_rejection").val();
    
      var other= $("form").find('#other').val();
       other=other==undefined?'':other;
       id= $('input[name=id]').val();
        $.ajax({
        url         : ajax.ajaxurl,
        data        : {action:'saveRejectionChanges',reason:reason_rejection,other:other,id:id},
        type        : 'POST',
        success     : function(data, textStatus, jqXHR){

            $('#rejected_Model').html(data);
            $('#rejectRequest').hide()
            var table = $('table').DataTable();
            table.ajax.reload()
        },
        error:function(e){
            console.log(e)
        }
    });
    }) 
});