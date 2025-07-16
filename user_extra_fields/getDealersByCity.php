<?php 
add_shortcode('getDealersByCity','getDealersByCity');
    function getDealersByCity()
    {
        if(! is_user_logged_in()){
            $url = wp_login_url();
          return  $table = "<a href=' $url'> Please Login</a>";
          wp_die();
        }
        global $wpdb;
        wp_register_style('select2css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css');
        wp_enqueue_script( 'select2js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js' );
      $table_name = $wpdb->prefix . "usermeta";   

       
        $query="SELECT DISTINCT meta_value FROM $table_name WHERE meta_key  LIKE '%city%' and (meta_value != '' and meta_value is not null)   ORDER BY meta_value ASC";
       $record=$wpdb->get_results($query , $output = OBJECT);
    
      $city_options='';
      foreach ( $record as $city )
      {
        $city_options .= "<option value=$city->meta_value> $city->meta_value </option>";
      }
      
      $html= "<div id='dealersBox'>
          
        

  
      <div class='cities_div col-6-b'>

        <select required name='city' id='cities'>
        <option value=''>Select Your City</option>
        $city_options
        </select>
    </div>
    <div class='dealer_div col-6-b'>
     <select required name='dealer_id' id='dealers'>
        <option value=''>Select Your Dealer </option>
        
        </select>
    </div>
    </div>
    <div id='dealer_display_info'></div> 
    
    
    ";
      
          return $html;
      }
function getDealers(){
    header("Content-Type: application/json");
       // Query for users based on the meta data
       $arr = array(
			'meta_key'	  =>	'city',
			'meta_value'	=>	trim($_POST['city'])
       );


    	$user_query = new WP_User_Query(
		array(
			'meta_key'	  =>	'city',
			'meta_value'	=>	trim($_POST['city'])
		)
	);	// Get the results from the query, returning the first user
    $users = $user_query->get_results();
    
    $options="<option value=''>Select Your Dealer</option>";
    foreach($users as $user){
        // echo '<pre>'; print_r($user); echo '</pre>'; die;
        $options.="<option value ='$user->id'>$user->first_name $user->last_name</option>";

        }
    echo json_encode($options);
	wp_die();
}

      add_action('wp_ajax_getDealers','getDealers'); 
      add_action('wp_ajax_nopriv_getDealers','getDealers');

function getUser(){
    // header("Content-Type: application/json");
   $user_id = $_POST['user_id'];
   $userinfo = get_user_meta($user_id,'',true); 
   //print_r($userinfo['first_name'][0]);
   //echo '<pre>'; print_r($userinfo); echo '</pre>'; die;
   $html="
    <div class='colmn'>
        <span class='label_'>City/Province </span>
        <span class='value'>".$userinfo['city'][0].' '.$userinfo['province'][0]."</span>
    </div>
    <div class='colmn'>
        <span class='label_'>DealerShip </span>
        <span class='value'>".$userinfo['first_name'][0].' '.$userinfo['last_name'][0]."</span>
    </div>
    <div class='colmn'>
        <span class='label_'>Phone Number </span>
        <span class='value'>".$userinfo['phone1'][0]."</span>
    </div>
 <div class='colmn'>
        <span class='label_'>Address </span>
        
        <span class='value'>".$userinfo['address'][0]."</span>
</div>
   ";

      echo  $html;  
      wp_die();     
 }
 
       add_action('wp_ajax_getUser','getUser'); 
       add_action('wp_ajax_nopriv_getUser','getUser');