<?php
//add additional emo to db

add_action('wp_ajax_wep_emo_new','webemailprotector_emo_new');

function webemailprotector_emo_new(){
 global $wpdb; //this is how your access the sql db
 check_ajax_referer( 'wep-sec', 'security' );
 $wep_nextemail = get_option('wepdb_nextemail');
 update_option('wepdb_nextemail',intval($wep_nextemail)+1);
 $i = $wep_nextemail ;
 add_option('wepdb_wep_entry_'.$i,'emo_'.$i);
 add_option('wepdb_wep_email_'.$i,'your email address '.$i);
 add_option('wepdb_wep_emo_'.$i,'xxxx-xxxx-xxxx-xxxx-xxxx');
 add_option('wepdb_wep_display_name_'.$i,'your web text '.$i);
 add_option('wepdb_wep_validated_'.$i,'false');
 $wep_nuemails = get_option('wepdb_nuemails');
 $wep_nuemails = intval($wep_nuemails)+1;
 update_option('wepdb_nuemails',$wep_nuemails);
 $arr = array(
  'row'=>$wep_nuemails,
  'id'=>$i
 );
 echo json_encode($arr);
 die();// you need this
}
//invoke the function
//webemailprotector_emo_new();

?>