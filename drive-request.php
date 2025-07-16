<?php
   /*
   Plugin Name: Drive Request
   Plugin URI: http://mtpixels.com
   description: This plugin created for suzuki drive request
   Version: 2.0
   Author: Engr.Nand Lal Bheel
   Author URI: http://jobee.pk/resume/nand
   License: GPL2
   */
  global $drive_request_db_version;
  $drive_request_db_version = '1.0';
 
  class DriveRequest{
    public function __construct()
    {
      register_activation_hook( __FILE__, array($this,'drive_request_install'));
      wp_register_style('custom_css',  plugin_dir_url( __FILE__ ).'style.css');
      wp_enqueue_style('custom_css');
    
      add_action('wp_ajax_saveDriveRequest',array($this,'saveDriveRequest')); 
      add_action('wp_ajax_nopriv_saveDriveRequest',array($this,'saveDriveRequest'));

      
      wp_enqueue_script('driveScript',  plugin_dir_url( __FILE__ ).'/js/driveScript.js', array('jquery'));
      wp_localize_script( 'driveScript', 'ajaxDriveRequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

      wp_enqueue_script('rejectRequest',  plugin_dir_url( __FILE__ ).'/js/rejectRequest.js', array('jquery'));
      wp_localize_script( 'rejectRequest', 'ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

      wp_localize_script( 'driveScript', 'ajaxLoadImage', plugin_dir_url( __FILE__ ).'ajax-loader.gif' );
     
      add_action('wp_ajax_requestAccepted',array($this,'requestAccepted')); 
      add_action('wp_ajax_nopriv_requestAccepted',array($this,'requestAccepted'));

      add_action('wp_ajax_completedRequest',array($this,'completedRequest'));
      add_action('wp_ajax_nopriv_completedRequest',array($this,'completedRequest'));
      
      add_action('wp_ajax_requestRejected',array($this,'requestRejected')); 
      add_action('wp_ajax_nopriv_requestRejected',array($this,'requestRejected'));

      add_action('wp_ajax_saveRejectionChanges',array($this,'saveRejectionChanges')); 
      add_action('wp_ajax_nopriv_saveRejectionChanges',array($this,'saveRejectionChanges'));
      
      add_action('wp_ajax_saveRequestChanges',array($this,'saveRequestChanges')); 
      add_action('wp_ajax_nopriv_saveRequestChanges',array($this,'saveRequestChanges'));

      add_action('wp_ajax_saveDealerRequest',array($this,'saveDealerRequest')); 
      add_action('wp_ajax_nopriv_saveDealerRequest',array($this,'saveDealerRequest'));

      wp_enqueue_script( 'sweetAlert', 'https://unpkg.com/sweetalert/dist/sweetalert.min.js' );
   
    } 
    public function drive_request_install(){
      global $wpdb;
      $table_name = $wpdb->prefix . "drive_requests"; 
      $charset_collate = $wpdb->get_charset_collate();
      
      $sql = "CREATE TABLE `$table_name`(
          `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `dealer_id` INT(10) UNSIGNED NOT NULL,
          `first_name` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `last_name` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `gender` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `age` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `city` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `email` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `mobile` VARCHAR(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `driving_licence` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `car_model` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `purchase_type` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `rating` TINYINT(3) UNSIGNED DEFAULT 0,
          `status` TINYINT(3) UNSIGNED DEFAULT 0,
          `reason_rejection` TINYTEXT  COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `date` TIMESTAMP NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        )  $charset_collate;";

          require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
          dbDelta( $sql );
          add_option( 'drive_request_db_version', $drive_request_db_version );
      }

    

      public  function saveDriveRequest()
      {
        unset($_POST['action']);
        global $wpdb;     
        $table_name = $wpdb->prefix."drive_requests"; 
        $wpdb->insert($table_name,$_POST);
      
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
        die();
      }
      public function saveDealerRequest(){

      }
   
      public function requestAccepted(){
        // 0 for padding, 1 for accepted , 2 for rejected 3 for completed(status info)
        $record_id = $_POST['record_id'];
        global $wpdb;
        $table_name = $wpdb->prefix . "drive_requests"; 
       
       $query="SELECT `email` FROM $table_name WHERE id = $record_id";
       $record=$wpdb->get_row($query , $output = OBJECT);
    
       $status= $wpdb->update( $table_name, $data=['status'=>1], $where=['id'=>$record_id ], $format = null, $where_format = null );
        
       if($status && $record->email){
        wp_mail($record->email, 'Your Test Driving Request Accepted',  $headers = '', $attachments = array() );
      }
      }
     public function completedRequest(){
        // 0 for padding, 1 for accepted , 2 for rejected 3 for completed(status info)
        $record_id = $_POST['id'];

        $data=$_POST;
        unset($data['action'],$data['id']);
        $data['status']=3;//completed;
        
        global $wpdb;
        $table_name = $wpdb->prefix . "drive_requests"; 
       
       $query="SELECT `email` FROM $table_name WHERE id = $record_id";
       $record=$wpdb->get_row($query , $output = OBJECT);
    
       $status= $wpdb->update( $table_name, $data, $where=['id'=>$record_id ], $format = null, $where_format = null );
        
       if($status && $record->email){
        wp_mail($record->email, 'Completed Your Testing',  $headers = '', $attachments = array() );
      }
      }

      public function requestRejected()
      { // 0 for padding, 1 for accepted , 2 for rejected (status info),3 for compeleted
      
        $record_id = $_POST['record_id'];
        

        $table='<div id="rejectRequest" class="modal">
       <!-- Modal content -->
         <div class="modal-content">
        <div class="modal-head">
         <div class="edit-heading"> <h4>Reject Drive Request</h4></div>
             <span  class="close">&times;</span></div>';
              $table.="<form id='rejection_form' >
                  <input type='hidden' name='id' value='$record_id'>";
                  
                  $table.="<div class='form-group row '>
                    <label for='reason_rejection' class='col-form-label col-md-4'>Reason for Reject:</label>
                      <select required name='reason_rejection' id='reason_rejection' class='col-md-8'>
                        <option value =''>Select Reason of Reject</option>
                        <option >Wrong Information</option>
                        <option >Taken From Other Dealer</option>
                        <option >Postponed</option>
                        <option >Not interested</option>
                        <option value='others' >Others Specify Reason </option>
                      </select>
                      
                  </div>
                  <div id='others'></div>";
                  

                   
               $table.="<div class='row modal_footer'>
               <div class='bottom-actions col-md-6'>
               <button type='submit' class='save-btn'>Save Changes</button>
               <button type='button' class='cancel-btn'>Cancel</button>
               </div>
              
               </div>
                </form></div> </div>";
       

            echo $table;
            wp_die();
           

        

      }
       public function saveRejectionChanges()
      {
        global $wpdb;
        $table_name = $wpdb->prefix . "drive_requests"; 

        $data['reason_rejection']=($_POST['reason']!='others')?$_POST['reason']:$_POST['other'];
         $data['status']=2;//2 for rejection
       
        $action=$wpdb->update( $table_name, $data,$where=['id'=>$_POST['id'] ], $format = null, $where_format = null );

        if($action){
          $status['msg']=1;
        }else{
          $status['msg']=0;
        }
        header('Content-Type: application/json');
        echo json_encode($status);
        wp_die();
      }
     
      public function saveRequestChanges(){
        
        $record_id=$_POST['id'];
        $data=[];
       
        global $wpdb;
        $table_name = $wpdb->prefix . "drive_requests"; 
        if(isset($_POST['driving_licence'])){
          $data['driving_licence']=$_POST['driving_licence'];
        }
        if(isset($_POST['car_model'])){
          $data['car_model']=$_POST['car_model'];
        }
        if(isset($_POST['purchase_type'])){
          $data['purchase_type']=$_POST['purchase_type'];
        }
        if(isset($_POST['age'])){
          $data['age']=$_POST['age'];
        }
        if(isset($_POST['rating'])){
          $data['rating']=$_POST['rating'];
        }
        
       $action= $wpdb->update( $table_name, $data, $where=['id'=>$record_id ], $format = null, $where_format = null );
       
       if($action){
          $status['msg']=1;
        }else{
          $status['msg']=0;
        }
        header('Content-Type: application/json');
        echo json_encode($status);
       wp_die();
      }
  }
add_role( 'dealer', "Dealer", array('read' => true) );
$driveRequest=new DriveRequest();



function add_datatables_scripts() {
  wp_register_script('datatables', 'https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js', array('jquery'), true);
  wp_enqueue_script('datatables');
      
  wp_register_script('datatables_bootstrap', 'https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js', array('jquery'), true);
  wp_enqueue_script('datatables_bootstrap');
}
  
function add_datatables_style() {
  wp_register_style('bootstrap_style', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
  wp_enqueue_style('bootstrap_style');
      
  wp_register_style('datatables_style', 'https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css');
  wp_enqueue_style('datatables_style');
}
  
add_action('wp_enqueue_scripts', 'add_datatables_scripts');
add_action('wp_enqueue_scripts', 'add_datatables_style');
 add_action('admin_enqueue_scripts', 'add_datatables_scripts');


//includes files
require_once(plugin_dir_path(__FILE__)."driveForm.php");
require_once(plugin_dir_path(__FILE__)."editDriveRequest.php");
require_once(plugin_dir_path(__FILE__)."viewDetailsRequest.php");
require_once(plugin_dir_path(__FILE__)."ajaxDTgetRejectRequests.php");
require_once(plugin_dir_path(__FILE__)."getPendingRequest.php");
require_once(plugin_dir_path(__FILE__)."ajaxDTgetAcceptedRequests.php");
require_once(plugin_dir_path(__FILE__)."user_extra_fields/addUserExtraFields.php");
require_once(plugin_dir_path(__FILE__)."user_extra_fields/getDealersByCity.php");
require_once(plugin_dir_path(__FILE__)."createRequestByDealer.php");
?>
