<?php 

add_action('wp_ajax_ajaxDTgetAcceptedRequest','ajaxDTgetAcceptedRequest'); 
add_action('wp_ajax_nopriv_ajaxDTgetAcceptedRequest','ajaxDTgetAcceptedRequest');




 //0 pending 1 accepted 2 reject 3 completed
function getAcceptedRequestDT_scripts() {
    wp_enqueue_script( 'rejectScript', plugin_dir_url(__FILE__).'js/table.js', array(), '1.0', true );
    wp_localize_script( 'rejectScript', 'ajax_url', admin_url('admin-ajax.php?action=getAcceptedRequestDT') );
}

function getAcceptedRequestDT() {
    if(! is_user_logged_in()){
        $url = wp_login_url();
      return  $table = "<a href=' $url'> Please Login</a>";
      die;
     
    }

    $user=wp_get_current_user();
    $role= $user->roles[0];
    $user_id=$user->ID;
    getAcceptedRequestDT_scripts(); 
    
    ob_start(); 
    
   $tblHTML='<div id ="popupModal"></div>
    <table id="getAcceptedRequestDT" class="table table-striped table-hover"> 
        <thead> 
        <tr> 
        <th>Sr#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Mobile#</th>
                <th>Email</th>
                <th>Rating</th>';
            if($role == 'administrator'){ 
                $tblHTML.='<th>Dealer</th>';

                 }
                $tblHTML.='<th>Action</th>';
            
$tblHTML.='</tr>
         </thead> 
    </table> ';
         
    return $tblHTML;
}
 
add_shortcode ('getAcceptedRequestDT', 'getAcceptedRequestDT');

add_action('wp_ajax_getAcceptedRequestDT', 'getAcceptedRequestDT_server_side_callback');
add_action('wp_ajax_nopriv_getAcceptedRequestDT', 'getAcceptedRequestDT_server_side_callback');
 
 
function getAcceptedRequestDT_server_side_callback() {
      header("Content-Type: application/json");
      $user=wp_get_current_user();
      $role= $user->roles[0];
      $user_id=$user->ID;
      global $wpdb;
      $table_name = $wpdb->prefix . "drive_requests";   

      $request= $_GET;

      if($role == 'administrator'){
        $columns = array(
            0   =>' Sr#',
            1   => 'first_name',
            2   => 'last_name',
            3   => 'mobile',
            4   => 'email',
            5   => 'rating',
            6   => 'dealer_id',
            7  => 'action'
    
        );
      }else{
        $columns = array(
            0   =>' Sr#',
            1   => 'first_name',
            2   => 'last_name',
            3   => 'mobile',
            4   => 'email',
            5   => 'rating',
            6   => 'action'
    
        );
      }
    
    $start= $request['start'];
    $limit= $request['length'];
    $sort_column=($request['order'][0]['column']!=0)?$columns[$request['order'][0]['column']]:'id';
    $sort_type=($request['order'][0]['column']!=0)?$request['order'][0]['dir']:'DESC';
    $searchable_text=$request['search']['value'];
    
    if($searchable_text == ''){
        if($role == 'administrator'){
            $query_record_count="SELECT count(id) as total_record FROM $table_name where (status = 1 or status = 3) ";
            $query_data="SELECT * FROM $table_name where (status = 1 or status = 3) ORDER BY $sort_column $sort_type limit $limit offset $start";
          
        }else{
            $query_record_count="SELECT count(id) as total_record FROM $table_name where (status = 1 or status = 3) and dealer_id =  $user_id ";
            $query_data="SELECT * FROM $table_name where dealer_id =  $user_id and (status = 1 or status = 3) ORDER BY $sort_column $sort_type limit $limit offset $start";
        }  
    }else{
        if($role == 'administrator'){
            $query_record_count="SELECT count(id) as total_record FROM $table_name where (status = 1 or status = 3) and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' )";
            $query_data="SELECT * FROM $table_name where (status = 1 or status = 3) and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' ) ORDER BY $sort_column $sort_type  limit $limit offset $start";
         
        }else{
            $query_record_count="SELECT count(id) as total_record FROM $table_name where (status = 1 or status = 3) and  dealer_id =  $user_id and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' )";
            $query_data="SELECT * FROM $table_name where (status = 1 or status = 3) and  dealer_id =  $user_id and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' ) ORDER BY $sort_column $sort_type  limit $limit offset $start";
         
        }  
    }
    $query_record_count = $wpdb->get_results($query_record_count);
    $query_data = $wpdb->get_results($query_data);
    $record_data=[];

    $loop=0;

   foreach($query_data as $key => $record){
       $stars='';
       for($i=1; $i <= 5;$i++){

            if($i<=$record->rating){
                $stars.="<span class='star-color'>&#9733;</span>";            
            }else{
                $stars.="<span>&#9733;</span>";
            }

        }
       $record_data[$loop][]=$loop+$start+1;
       $record_data[$loop][]=$record->first_name;
       $record_data[$loop][]=$record->last_name;
       $record_data[$loop][]=$record->mobile;
       $record_data[$loop][]=$record->email;
       $record_data[$loop][]=$stars;
       if($role == 'administrator'){ 
        $dealer =get_user_by( 'id',  $record->dealer_id);
        $record_data[$loop][]= $dealer->first_name . ' '.$dealer->last_name ;
       }

        if($record->status == 1){
            $record_data[$loop][]="<button record_id=$record->id  class ='editRequest'>Edit</button>
                             <button record_id=$record->id  class ='viewDetailsRequest'>View</button>";

        }else{
              $record_data[$loop][]="<button record_id=$record->id  class ='Completed'>Completed</button>";

        }
       
       $loop++;
   }

   $data = array(
        'draw' => $draw,
        'recordsTotal' =>$query_record_count[0]->total_record,
        'recordsFiltered' => $query_record_count[0]->total_record,
        'data' => $record_data,
    );
     
      echo json_encode($data);
    wp_die();
 
}

