<?php 

add_action('wp_ajax_ajaxDTgetPaddingRequest','ajaxDTgetPaddingRequest'); 
add_action('wp_ajax_nopriv_ajaxDTgetPaddingRequest','ajaxDTgetPaddingRequest');


 //0 pending 1 accepted 2 reject 3 completed


function getPaddingRequestDT_scripts() {
    wp_enqueue_script( 'paddingScript', plugin_dir_url(__FILE__).'js/table.js', array(), '1.0', true );
    wp_localize_script( 'paddingScript', 'ajax_url', admin_url('admin-ajax.php?action=getPaddingRequestDT') );
}

function getPendingRequestDT() {

    if(! is_user_logged_in()){
        $url = wp_login_url();
      return  $table = "<a href=' $url'> Please Login</a>";
      die;
     
    }
    $user=wp_get_current_user();
    $role= $user->roles[0];
    $user_id=$user->ID;
    getPaddingRequestDT_scripts(); 
    
    ob_start();
    $tbHtml='<div id="rejected_Model"></div>';
    $tbHtml.='<table id="getPendingRequestDT" class="table table-striped table-hover"> 
        <thead> 
        <tr> 
        <th>Sr#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Mobile#</th>
                <th>Email</th>';
                
            if($role == 'administrator'){ 
                $tbHtml.='<th>Dealer</th>';

             }
             $tbHtml.='<th>Action</th>
            
            </tr> 
        </thead> 
    </table> ';
         
    return $tbHtml;
}
 
add_shortcode ('getPendingRequestDT', 'getPendingRequestDT');

add_action('wp_ajax_getPaddingRequestDT', 'getPaddingRequestDT_server_side_callback');
add_action('wp_ajax_nopriv_getPaddingRequestDT', 'getPaddingRequestDT_server_side_callback');
 
 
function getPaddingRequestDT_server_side_callback() {
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
            5   => 'dealer_id',
            6  => 'action'
    
        );
      }else{
        $columns = array(
            0   =>' Sr#',
            1   => 'first_name',
            2   => 'last_name',
            3   => 'mobile',
            4   => 'email',
            5   => 'action'
    
        );
      }
    
    $start= $request['start'];
    $limit= $request['length'];
    $sort_column=($request['order'][0]['column']!=0)?$columns[$request['order'][0]['column']]:'id';
    $sort_type=($request['order'][0]['column']!=0)?$request['order'][0]['dir']:'DESC';
    $searchable_text=$request['search']['value'];
    
    if($searchable_text == ''){
        if($role == 'administrator'){
            $query_record_count="SELECT count(id) as total_record FROM $table_name where status = 0";
            $query_data="SELECT * FROM $table_name where status = 0 ORDER BY $sort_column $sort_type limit $limit offset $start";
          
        }else{
            $query_record_count="SELECT count(id) as total_record FROM $table_name where status = 0 and dealer_id =  $user_id ";
            $query_data="SELECT * FROM $table_name where dealer_id =  $user_id and status = 0 ORDER BY $sort_column $sort_type limit $limit offset $start";
        }  
    }else{
        if($role == 'administrator'){
            $query_record_count="SELECT count(id) as total_record FROM $table_name where status = 0 and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' )";
            $query_data="SELECT * FROM $table_name where status = 0  and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' ) ORDER BY $sort_column $sort_type  limit $limit offset $start";
         
        }else{
            $query_record_count="SELECT count(id) as total_record FROM $table_name where status = 0 and  dealer_id =  $user_id and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' )";
            $query_data="SELECT * FROM $table_name where status = 0 and  dealer_id =  $user_id and (first_name like '%$searchable_text%' or last_name like '%$searchable_text%' or email like  '%$searchable_text%' or phone like '%$searchable_text%' ) ORDER BY $sort_column $sort_type  limit $limit offset $start";
         
        }  
    }
    $query_record_count = $wpdb->get_results($query_record_count);
    $query_data = $wpdb->get_results($query_data);
    $record_data=[];
$loop=0;
   foreach($query_data as $key => $record){
       $record_data[$loop][]=$loop+$start+1;
       $record_data[$loop][]=$record->first_name;
       $record_data[$loop][]=$record->last_name;
       $record_data[$loop][]=$record->mobile;
       
       $record_data[$loop][]=$record->email;
       if($role == 'administrator'){ 
        $dealer =get_user_by( 'id',  $record->dealer_id);
        $record_data[$loop][]= $dealer->first_name . ' '.$dealer->last_name ;
       }
       $record_data[$loop][]="<button  record_id= $record->id  class ='acceptRequest'>Accept</button>|<button  record_id=$record->id class='rejectRequest'>Reject</button>";
       $loop++;
   }

  // print_r($record_data);
    $data = array(
        'draw' => $draw,
        'recordsTotal' =>$query_record_count[0]->total_record,
        'recordsFiltered' => $query_record_count[0]->total_record,
        'data' => $record_data,
    );
     
      echo json_encode($data);
    wp_die();
 
}