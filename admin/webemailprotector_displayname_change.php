<?php
//change display name in db

add_action('wp_ajax_wep_displayname_change','webemailprotector_displayname_change');

function webemailprotector_displayname_change(){
 global $wpdb; //this is how your access the sql db
 check_ajax_referer( 'wep-sec', 'security' );
 if($_SERVER['REQUEST_METHOD']=='POST'){
  $i=$_POST['emo_nu'];
  $displayname=$_POST['displayname']; 
  //$displayname=str_replace("'", "", $displayname);
  //$displayname=str_replace('"',  '', $displayname);
  if (ctype_digit($i)){
   update_option('wepdb_wep_display_name_'.$i,$displayname);
  }
  echo $displayname;
 }
 die(); // you need this
}

//webemailprotector_displayname_change();

?>