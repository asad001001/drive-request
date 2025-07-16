<?php 
 add_action('wp_ajax_viewDetails','viewDetails'); 
 add_action('wp_ajax_nopriv_viewDetails','viewDetails');

 function viewDetails(){
    global $wpdb;
    $record_id= $_POST['record_id'];
    $table_name = $wpdb->prefix . "drive_requests"; 
    $query="SELECT * FROM $table_name WHERE id = $record_id";
    $record=$wpdb->get_row($query , $output = OBJECT);
     
     


$html='<div id="viewDetails" class="modal">
  <div class="modal-content">
  <div class="modal-head">
         <div class="edit-heading">';
           $html.="<h4> $record->first_name  $record->last_name</h4></div>";
    $html.='<span class="close">&times;</span>
    
    </div>
    <div class="modal-body">';
    $html.="<div class='row'>
                <label class='col-sm-6'>Email</label>
                <div class='col-sm-6'>
                 $record->email  
                </div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>Age</label>
                <div class='col-sm-6'>
                 $record->age  
                </div>
        </div>
        <div class='row'>
        <label class='col-sm-6'>Gender</label>
        <div class='col-sm-6'>
         $record->gender  
        </div>
</div>
        <div class='row'>
                <label class='col-sm-6'>Mobile#</label>
                <div class='col-sm-6'>
                  $record->mobile  
                </div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>City</label>
                <div class='col-sm-6'>
                  $record->city  
                </div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>Purchase Type</label>
                <div class='col-sm-6'>".
                  $record->purchase_type."</div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>Car Model</label>
                <div class='col-sm-6'>".
                 strtoupper( $record->car_model) .
        "</div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>Licence</label>
                <div class='col-sm-6'>".
                strtoupper( $record->driving_licence).
          "</div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>Test Drive Experience</label>
                <div class='col-sm-6'>";
                   
                       
                     for($i=1; $i <= 5;$i++){

            if($i<=$record->rating){
                $html.="<span class='star-color'>&#9733;</span>";            
            }else{
                $html.="<span>&#9733;</span>";
            }

        }
               $html.="</div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>Status</label>
                <div class='col-sm-6'>".
                ( $record->status==0?'Padding':(($record->status==1)?'Accepted':'Rejected')).
                "</div>
        </div>
        <div class='row'>
                <label class='col-sm-6'>Date</label>
                <div class='col-sm-6'>".
                 date("d-m-Y", strtotime( $record->date)).
                "</div>
        </div>
        </div>

        </div>

        </div> ";
        echo $html;
        wp_die();
  

  }