<?php
//change email address in db

add_action('wp_ajax_wep_email_get','webemailprotector_email_get');

function webemailprotector_email_get(){
 global $wpdb; //this is how your access the sql db
 check_ajax_referer( 'wep-sec', 'security' );
 if($_SERVER['REQUEST_METHOD']=='GET'){
  $i=$_GET['emo_nu'];
  if (ctype_digit($i)){
   $email=get_option('wepdb_wep_email_'.$i);
   echo $email;
  }
 }
 die();// you need this
}

?>