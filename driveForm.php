<?php 
add_shortcode('drive_request_form','drive_request_form');
    function drive_request_form()
    {
      global $wpdb;
  /*     $args = [
        'role'    => 'dealer',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
      ];
      $users = get_users( $args );
      $user_options='';
      foreach ( $users as $user )
      {
        $user_options .= "<option value=$user->id> $user->first_name $user->last_name </option>";
      } */
      //get all cities
      $table_name = $wpdb->prefix . "usermeta";   

       
     $query="SELECT DISTINCT meta_value FROM $table_name WHERE meta_key  LIKE '%city%' and (meta_value != '' and meta_value is not null)   ORDER BY meta_value ASC";
     $record=$wpdb->get_results($query , $output = OBJECT);
  
    $cities_options='';
    foreach ( $record as $city )
    {
      $cities_options .= "<option value=$city->meta_value> $city->meta_value </option>";
    }





      $users = get_users( $args );
      $month = date('m');
      $day = date('d');
      $year = date('Y');
      
      $today = $year . '-' . $month . '-' . $day;
      $form= "<div id='save_alert'></div> <form  id='drive_request'>
          
        <div class='field-set'>
          <input type='text' required name='first_name' value='' id='first_name' placeholder='First Name'>
          <input type='text' required name='last_name' value='' id='last_name' placeholder='Last Name'>
         <div class=' req-radio-buttons'>
            <div class='radiobtn'>
              <input type='radio' id='gender_m' name='gender' value='Male' checked/>
              <label for='gender_m'>Male</label>
            </div>
              <div class='radiobtn'>
              <input type='radio' id='gender_f' name='gender' value='Female' />
              <label for='gender_f'>Female</label>
            </div>
            </div>
          </div>
        <div class='field-set'>
          <input type='text' name='mobile' value='' id='mobile' placeholder='Mobile'>
          <input type='email'  name='email' value='' id='email' placeholder='Email '>
          <input type='date' required name='date' value='$today'  id='date' placeholder='Date'>
        </div>
        <div class='field-set'>
            <select required name='city' id='cities'>
            <option value=''>Select Your City</option>
            $cities_options
            </select>
            <select required name='dealer_id' id='dealers'>
             <option value=''>Dealer</option>
            </select>
        </div>
      
        
        
      
        <input type='submit' name='submit' value='Submit' id='submit' >

        </form><div id='loading'></div>";
      
          return $form;
      }