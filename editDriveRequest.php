<?php
 //0 pending 1 accepted 2 reject 3 completed
  add_action('wp_ajax_editRequest','editRequest'); 
  add_action('wp_ajax_nopriv_editRequest','editRequest');

function editRequest(){
 $table='';
global $wpdb;
    $record_id= $_POST['record_id'];
    $table_name = $wpdb->prefix . "drive_requests"; 
    $query="SELECT * FROM $table_name WHERE id = $record_id";
    $record=$wpdb->get_row($query , $output = OBJECT);
    $table.='<div id="editRequest" class="modal">
       <!-- Modal content -->
         <div class="modal-content">
        <div class="modal-head">
         <div class="edit-heading"> <h4>Edit Test Drive Request</h4></div>
             <span  class="close">&times;</span></div>';
              $table.="<form id='change_record_save' >
                  <input type='hidden' name='id' value='$record->id'>
                  <div class='form-group row'>
                    <label for='driving_licence' class='col-form-label col-md-4'>Driving Licence:</label>
                    <input type='text' name='driving_licence' class='col-md-8' value='$record->driving_licence ' id='driving_licence'>
                  </div>

                  <div class='form-group row'>
                    <label for='car_model' class='col-form-label col-md-4'>Car Model:</label>
                    <input type='text' name='car_model' class=' col-md-8' value='$record->car_model ' id='car_model'>
                  </div>";
                  
                  $table.="<div class='form-group row'>
                    <label for='purchase_type' class='col-form-label col-md-4'>Purchase Type:</label>
                      <select name='purchase_type' id='purchase_type'>
                        <option>Select Type</option>
                        <option ".((trim($record->purchase_type) == 'First Purches')?'selected':'')." >First Purches</option>
                        <option ".((trim($record->purchase_type) == 'Exchange / Replacement')?'selected':'')." >Exchange / Replacement </option>
                        <option ".((trim($record->purchase_type) == 'Additional Car')?'selected':'')." >Additional Car</option>
                      </select>
                  </div>";
                  $table.="<div class='form-group row'>
                    <label for='age' class='col-form-label col-md-4'>Age:</label>
                      <select name='age' id='age'>
                        <option value=''>Select Age(Years)</option>
                        <option ".((trim($record->age) == '0-25')?'selected':'')." >0-25</option>
                        <option ".((trim($record->age) == '26-35')?'selected':'')." >26-35</option>
                        <option ".((trim($record->age) == '36-45')?'selected':'')." >36-45</option>
                        <option ".((trim($record->age) == '45-above')?'selected':'')." >45-Above</option>
                      </select>
                  </div>";

                   if(!$record->rating):

                    $table.="<div class='form-group row'>
                    <label for='car_model' class='col-form-label col-md-4'>Rating:</label>
                    <div class='btn-group btn-group-toggle col-md-8' data-toggle='buttons'>
                      <label class='radio'>
                        <input type='radio' name='rating'  value=1 id='rating1' autocomplete='off' > 
                         <span class='radio-label'>1</span>
                      </label>
                      <label class='radio'>
                        <input type='radio' name='rating' value=2 id='rating2' autocomplete='off' > 
                         <span class='radio-label'>2</span>
                      </label>
                       <label class='radio '>
                        <input type='radio' name='rating' value=3 id='rating3' autocomplete='off' > 
                         <span class='radio-label'>3</span>
                      </label>
                      <label class='radio '>
                        <input type='radio' name='rating' value=4 id='rating4' autocomplete='off' > 
                        <span class='radio-label'>4</span>
                      </label>
                      <label class='radio '>
                        <input type='radio' name='rating'  value=5 id='rating5' autocomplete='off' > 
                        <span class='radio-label'>5</span>
                      </label>
                    </div>
                </div>";
              endif;
               $table.="<div class='row modal_footer'>
               <div class='bottom-actions col-md-6'>
               <button type='submit' class='save-btn'>Save Changes</button>
               <button type='button' class='cancel-btn'>Cancel</button>
               </div>
               <div class='col-md-6 text-right'>
               <button type='button' record_id='$record_id' class='complete-btn'>Mark as Complete</button>
               </div>
               </div>
                </form></div> </div>";
       

            echo $table;
            wp_die();
            
    //echo $table;
  }