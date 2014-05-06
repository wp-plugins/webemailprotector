<?php
//add additional emo to db

add_action('wp_ajax_wep_emo_delete','webemailprotector_emo_delete');

function webemailprotector_emo_delete(){
 global $wpdb; //this is how your access the sql db
 check_ajax_referer( 'wep-sec', 'security' );
 if($_SERVER['REQUEST_METHOD']=='POST'){
   $i=$_POST['emo_nu'];
   if (ctype_digit($i)){
    $wep_nuemails = get_option('wepdb_nuemails');
    delete_option('wepdb_wep_entry_'.$i);
    delete_option('wepdb_wep_email_'.$i);
    delete_option('wepdb_wep_emo_'.$i);
    delete_option('wepdb_wep_display_name_'.$i);
    delete_option('wepdb_wep_validated_'.$i);
    //decrement the $wep_nuemails options in db
    $wep_nuemails = intval($wep_nuemails)-1;
    update_option('wepdb_nuemails',$wep_nuemails);
    $arr = array(
     'emo_nu'=>$i,
     'nuemails'=>$wep_nuemails
    );
    echo json_encode($arr);
   }
 }
 die();
}

//invoke the function
//webemailprotector_emo_delete();
?>