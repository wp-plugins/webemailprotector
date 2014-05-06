<?php
/**
 * Plugin Name: WebEmailProtector
 * Plugin URI: http://www.webemailprotector.com
 * Description: ** Brand new to WordPress ** Secure your website email addresses. WebEmailProtector is the most secure Email Obfuscator available for websites. Email harvesting is a major problem for sites, as often this leads to unwanted spam, viruses and ID theft. But using this plugin your address is no longer openly listed. It's not just a simple encoder, but a secure authentication lookup with deep qualification. Thus it completely prevents spambots and other harvesters, machine or human, and stops email misuse at source. Easy to use and easy to set up, much less cumbersome and much more effective than a contact form or captcha.
 * Version: 1.0
 * Author: David Srodzinski
 * Author URI: http://www.webemailprotector.com/about.html
 * License: GPL2
*/

/*  Copyright 2013 DAVID SRODZINSKI WEBEMAILPROTECTOR  (email : david@webemailprotector.com)

    This program is free software for a period; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation. 
    
    The EMO API license key provided requires to be activated after 6 months if
    you are to continue using it. This is on a annual subscription basis unless otherwise stated on the 
    web site. Please visit http://www.webemailprotector.com.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

// be sure to edit in Notepad++ and save with option without BOM otherwise strange character errors result //

//add the additional php modules that do the db updates from the ajax calls
require_once(plugin_dir_path(__FILE__).'admin/webemailprotector_emo_new.php'); //adds new entry
require_once(plugin_dir_path(__FILE__).'admin/webemailprotector_emo_delete.php'); //deleted entry
require_once(plugin_dir_path(__FILE__).'admin/webemailprotector_emo_validate.php'); //validates entry
require_once(plugin_dir_path(__FILE__).'admin/webemailprotector_email_change.php'); //updates email in db
require_once(plugin_dir_path(__FILE__).'admin/webemailprotector_displayname_change.php'); //updates display text in db
require_once(plugin_dir_path(__FILE__).'admin/webemailprotector_email_get.php'); //retrieves email address from db

// function to add script code to <head>
function webemailprotector_insertheaderscript() {
 $scripturl = plugin_dir_url(__FILE__).'scripts/webemailprotector_headerscripts.js';
 if (!is_admin()) { //any java for published
  wp_enqueue_script('jquery'); //incase not already loaded
  wp_enqueue_script('webemailprotector_headerscripts',$scripturl,array('jquery'));
 }
 $scripturl = plugin_dir_url(__FILE__).'scripts/webemailprotector_adminscripts.js';
 if (is_admin()) { //any java for settings etc
  wp_enqueue_script('jquery'); //incase not already loaded
  wp_enqueue_script('webemailprotector_adminscripts',$scripturl);
  wp_localize_script( 'webemailprotector_adminscripts', 'MyAjax', array(
    // URL to wp-admin/admin-ajax.php to process the request
    'ajaxurl'          => admin_url( 'admin-ajax.php' ),
    // nonce to check security with ajax calls
    'security' => wp_create_nonce( 'wep-sec' ),
    ));
  }
}
//do it
add_action('wp_print_scripts','webemailprotector_insertheaderscript');

//function to style admin settings pages from the local css
function webemailprotector_admin_style() {
	$cssurl = plugin_dir_url(__FILE__).'css/webemailprotector_adminsettings.css';
    wp_enqueue_style('my-admin-theme', $cssurl);
}
add_action('admin_enqueue_scripts', 'webemailprotector_admin_style');

// function to add settings link on plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'webemailprotector_settings_link');
function webemailprotector_settings_link($links) {
 $settings_link='<a href="options-general.php?page=webemailprotector_plugin_options.php">Settings</a>';
 array_unshift($links,$settings_link);
 return $links;
}

// function to add plugin settings to admin sidebar
add_action('admin_menu','webemailprotector_admin_sidemenu');
function webemailprotector_admin_sidemenu() {
  add_options_page('My Plugin Options', 'WebEmailProtector', 'manage_options', 'webemailprotector_plugin_options', 'webemailprotector_plugin_options');
}

//this is the main function to add the settings logic and markup
function webemailprotector_plugin_options() {
  if (!current_user_can('manage_options')) {
    wp_die( __('You do not have sufficient permissions to access this page.'));
  }
  //setup secure code
  $ajax_nonce = wp_create_nonce( "wep-sec-string" );
  // any emails stored?
  // reset database during dev only (can remove later) or on first instantiation create 5 blanks
  $wep_reset_db = false;
  if (( $wep_reset_db == true ) or ( get_option('wepdb_nuemails') == false )){
   if ( get_option('wepdb_nuemails') == true) {delete_option('wepdb_nuemails');}
   add_option('wepdb_nuemails','5'); //this holds the number stored in the dB
   if ( get_option('wepdb_nextemail') == true) {delete_option('wepdb_nextemail');}
   add_option('wepdb_nextemail','6'); //this holds the next one to add to aid delete & refresh operations
   $wep_nuemails = get_option('wepdb_nuemails');
   for ($i = 1;$i <=$wep_nuemails; $i++) {
    if ( get_option('wepdb_wep_entry_'.$i) == true ) { delete_option('wepdb_wep_entry_'.$i) ;}
	add_option('wepdb_wep_entry_'.$i,'emo_'.$i);
   	if ( get_option('wepdb_wep_email_'.$i) == true ) { delete_option('wepdb_wep_email_'.$i) ; }
	add_option('wepdb_wep_email_'.$i,'email@yourdomain.com');
    if ( get_option('wepdb_wep_emo_'.$i) == true ) { delete_option('wepdb_wep_emo_'.$i) ; }
	add_option('wepdb_wep_emo_'.$i,'xxxx-xxxx-xxxx-xxxx-xxxx');
    if ( get_option('wepdb_wep_display_name_'.$i) == true ) { delete_option('wepdb_wep_display_name_'.$i) ; }
    add_option('wepdb_wep_display_name_'.$i,'Email Us');
	if ( get_option('wepdb_wep_validated_'.$i) == true) { delete_option('wepdb_wep_validated_'.$i) ; }
	add_option('wepdb_wep_validated_'.$i,'false');
    }
  }
  // if so then load up the data to local variables for displaying
  if ( get_option('wepdb_nuemails') == true ) { 
   $wep_nextemail = get_option('wepdb_nextemail');
   $l=0;
   for ($i = 1;$i <$wep_nextemail; $i++) {
    //when refresh get rid of any old db that have been deleted to auto compress db
	if (get_option('wepdb_wep_entry_'.$i) == false) { //then has been deleted and need to cleanse that line of the db and shuffle it
	  //do nowt as empty
	  }
	else {
	 $l++;
	 update_option('wepdb_wep_entry_'.$l,'emo_'.$l);
	 ${'wep_email_'.$l} = get_option('wepdb_wep_email_'.$i);
	 update_option('wepdb_wep_email_'.$l,${'wep_email_'.$l});
     ${'wep_emo_'.$l} = get_option('wepdb_wep_emo_'.$i);
	 update_option('wepdb_wep_emo_'.$l,${'wep_emo_'.$l});
     ${'wep_display_name_'.$l} = get_option('wepdb_wep_display_name_'.$i);
	 update_option('wepdb_wep_display_name_'.$l,${'wep_display_name_'.$l});
     ${'wep_validated_'.$l} = get_option('wepdb_wep_validated_'.$i);
	 update_option('wepdb_wep_validated_'.$l,${'wep_validated_'.$l});
	 }
	}
	//delete any left over crud
    for ($i = ($l+1); $i <$wep_nextemail; $i++) {
     delete_option('wepdb_wep_entry_'.$i);
     delete_option('wepdb_wep_email_'.$i);
     delete_option('wepdb_wep_emo_'.$i);
     delete_option('wepdb_wep_display_name_'.$i);
     delete_option('wepdb_wep_validated_'.$i);
    }
   $wep_nuemails=$l;
   update_option('wepdb_nuemails',$l);
   $wep_nextemail = intval($wep_nuemails)+1;
   update_option('wepdb_nextemail',$wep_nextemail); //update on refresh so always pointing to next new one to add
  }
  // do the display stuff
  echo '<div class="webemailprotector_admin_wrap">';
  echo '<br>';
  echo '<h1><blue>Web</blue><green>Email</green><red>Protector</red> &nbsp;&nbsp;&nbsp;&nbsp;WordPress Plugin Settings Menu<br>';
  echo '<p style="font-size:12px;margin-top:0;margin-left:0;"><i>&nbsp;&nbsp;securing your web-site email addresses</i></p>';
  echo '</h1>';
  echo '<p>Using the form below, please enter each email address that you wish to use into the <b>secured email address</b> column.</p>';
  echo '<p>Next enter the associated text to display into the <b>displayed text</b> column (this is the link text that will appear on your pages</p>';
  echo '<p>in place of the email address when published). Then follow the further instructions to register, validate and use each email.</p>';
  echo '<form action="" name="wep_settings" method="POST">';
  echo '<table id="wep_table"><tbody>';
  echo '<tr>';
  echo '<th>secured email address </th>';
  echo '<th>displayed text</th>';
  echo '<th colspan="2">actions</th>';
  echo '</tr>';
  $php_pathname='\''.plugin_dir_url(__FILE__).'admin'.'\'';
  for ($i = 1;$i <=$wep_nuemails; $i++) {
   echo '<tr id="wep_tablerow_'.$i.'">';
   $emo_email = ${'wep_email_'.$i};
   $display_name = ${'wep_display_name_'.$i};
   $validated = ${'wep_validated_'.$i};
   if ($validated == 'false') {$color='color:red';}
   else {$color='color:green';}
   echo '<td><input type="text" id="wep_emailtxt_'.$i.'" style="'.$color.';" onkeyup="webemailprotector_email_change(\''.$i.'\',this.value)" name="wep_email_'.$i.'" value="'.$emo_email.'"></td>';
   echo '<td><input type="text" id="wep_displaytxt_'.$i.'" onkeyup="webemailprotector_displayname_change(\''.$i.'\',this.value)" name="wep_name_'.$i.'" value="'.$display_name.'"></td>';
   echo '<td><input id="wep_validate_'.$i.'" type="button" class="button add another" value="validate" onclick="webemailprotector_validate(\''.$i.'\')"></td>';
   echo '<td><input id="wep_delete_'.$i.'" type="button" class="button add another" value="delete" onclick="webemailprotector_emo_delete(\''.$i.'\')"></td>';
   echo '</tr>';
  }
  echo '</tbody>';
  echo '<tr><td><green>green=validated</green>/<red>red=unvalidated</red></td></tr>';
  echo '<tr></tr>';
  echo '<tr><td>';  
  echo '<input id="submit" class="button add another" type="button" value="add another" onclick="webemailprotector_emo_new('.$php_pathname.')">';
  echo '</td></tr>';
  echo '</table>';
  //script to keep table updated on refresh properly
  echo '<script>';
  for ($i=1; $i<=$wep_nuemails ; $i++) {
  echo 'document.getElementById(\'wep_emailtxt_'.$i.'\').value = "'.${'wep_email_'.$i}.'";';
  echo 'document.getElementById(\'wep_displaytxt_'.$i.'\').value = "'.${'wep_display_name_'.$i}.'";';
  }
  echo '</script>';
  echo '<p style="margin-left:400px">';
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  //echo '<input id="submit" class="button button-primary" type="submit" value="update"></input>';
  echo '</p>';
  echo '</form>';
  echo '<p>Instructions:</p>';
  echo '<p>1. To use simply place any of the above secured email addresses within square brackets (e.g. [email@yourdomain.com]) on your <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WordPress pages.</p>';
  echo '<p>2. Each email address must be registered with us in order to provide the necessary security. If you have not registered an email <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;address please do so by visiting <a target="_blank" href="http://www.webemailprotector.com/signup_wp.html">register</a>.</p>';
  echo '<p>4. Then check that the email address has been registered correctly using the <input id="submit" type="button" class="button add another" value="validate"> button.</p>';
  echo '<p>5. You can tell when the email address registration was successful because you get a pop-up confirmation message and the <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;email text color will turn from red to green.</p>';
  echo '<p>6. Your can add additional email addresses using the <input id="submit" class="button add another" type="button" value="add another"> button.</p>';
  echo '<p>7. The <b>displayed text</b> column is for you to edit and set up as you like. We suggest that you do not use the email address itself as <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;this will still leave you vulnerable.</p>';
  echo '<p>8. As an option you can change the style of the email address appearance using CSS. For those familiar with CSS use the class <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"wep_email" of the &#60;a&#62; element using the selector a.wep_email {}.</p>';
  echo '<p><br></p>';

  //set up the spinner
  echo '<div id="wep_spinner">';
  echo '<img src="'.plugin_dir_url(__FILE__).'images/wep_spinner.gif"/>';
  echo '<p> please wait while we connect to the server to verify your code . . . </p>';
  echo '</div>';
  echo '<div id="wep_dullout">';
  echo '<p><br></p>'; // to force it to display
  echo '</div>';
  echo '</div>'; //of the whole thing
}

// do the filter on any displayed page
function webemailprotector_filter($content) {
//do it on the page (rather than a blog?) and not on the admin pages themselves
if (is_page() and !is_admin()) {
 // populate dataset
 //$newcontent = str_replace('@webemailprotector.com','@balls.com',$content);
 $newcontent=$content; //copy the page
 $wep_nuemails = get_option('wepdb_nuemails');
 for ($i = 1;$i <= $wep_nuemails; $i++) {
    $wep_email = get_option('wepdb_wep_email_'.$i);
    $wep_emo = get_option('wepdb_wep_emo_'.$i);
    $wep_display_name = get_option('wepdb_wep_display_name_'.$i);
    $newtext='<a class="wep_email" href="JavaScript:emo(\''.$wep_emo.'\')" title="'.$wep_display_name.'">'.$wep_display_name.'</a>';
    //of form [email]
	$newcontent = str_replace('['.$wep_email.']',$newtext,$newcontent); //email in brackets[]
  }
 return  $newcontent;
 }
}
add_filter('the_content','webemailprotector_filter');

?>