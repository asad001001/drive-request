<?php 
add_action('wp_ajax_saveDealerRequest','saveDealerRequest'); 
add_action('wp_ajax_nopriv_saveDealerRequest','saveDealerRequest');

add_shortcode('createRequestByDealer','createRequestByDealer');
    function createRequestByDealer()
    {
      global $wpdb;
      $table_name = $wpdb->prefix . "usermeta";   
      if(! is_user_logged_in()){
        $url = wp_login_url();
      return  $table = "<a href=' $url'> Please Login</a>";
      wp_die();
      }
      $user = wp_get_current_user();
    if ( ! in_array( 'dealer', (array) $user->roles ) ) {
      return  $table = "<h4>Sorry! Only dealer has right to access</h4";
      wp_die();
    }
    $month = date('m');
    $day = date('d');
    $year = date('Y');
    
    $today = $year . '-' . $month . '-' . $day;
      $cDate=date("d/m/Y");
      $form= "<div id='save_alert'></div> 
      <form  id='DealerRequestForm'>    
        <div class='field-set row '>
          <input type='text' required name='first_name' value='' id='first_name' placeholder='First Name' >
          <input type='text' required name='last_name' value='' id='last_name' placeholder='Last Name' >
              <div class=' dealer-radio-buttons'>  
              <input type='radio' name='gender' value='Male' id='gender_m' value='male' placeholder='Male'><label for='gender_m'>Male</label>
              <input type='radio' name='gender' value='Female' id='gender_f' value='female' placeholder='Female'><label for='gender_f'>Female</label>
              </div>
        </div>
        <div class='field-set row '>
            <input type='text' name='mobile' value='' id='mobile' class=' col-md-4' placeholder='Mobile'>
            <input type='email'  name='email' value='' class=' col-md-4' id='email' placeholder='Email '>
            <input type='text' placeholder='Driving Licence' name='driving_licence' class=' col-md-4' value='' id='driving_licence'>
        </div>
        <div class='field-set row '>
           <select name='car_model'  id='car_model'   >
            <option value=''>Select Car Model</option>
            <option >Alto VX</option>
            <option>Alto VXR</option>
            <option>Alto VXL</option>
          </select>
          <input type='date' required name='date' value='$today'  id='date' placeholder='Date' >
         <select name='purchase_type' id='purchase_type' >
            <option value='' >Purchase Type</option>
            <option >First Purches</option>
            <option >Exchange / Replacement </option>
            <option >Additional Car</option>
          </select>
          <select name='age' id='age' >
            <option value=''>Select Age</option>
            <option  >0-25</option>
            <option >26-35</option>
            <option  >36-45</option>
            <option >45-Above</option>
          </select>
        </div>
      <div class='dealer-submit'>
        <input type='submit' name='submit' value='Submit' id='submit' ></div>

        </form><div id='loading'></div>";
      
          return $form;
      }
      function saveDealerRequest(){
        global $wpdb;     
        $table_name = $wpdb->prefix."drive_requests"; 

        $dealer_id=get_current_user_id();
        $city = get_user_meta($dealer_id,'city')[0];
        $data=$_POST;
        $data['city']= $city;
        $data['dealer_id']=$dealer_id;
        $data['status']=1;//1 for accept
        unset($data['action']);

        
        $wpdb->insert($table_name,$data);
      
        if($wpdb->insert_id){
          if(isset($_POST['email'])){
           
            wp_mail($_POST['email'], 'Request Submited For Test Drive','Thanks Dear for Interset with our products',  $headers = '', $attachments = array() );
          }
          $action=['msg'=>1];
        }else{
        $action=['msg'=>0];
        }
        header('Content-Type: application/json');
        echo json_encode($action);
       wp_die();
        
      }