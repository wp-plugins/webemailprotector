<?php
/*
 * Plugin Name: WebEmailProtector
 * Plugin URI: http://www.webemailprotector.com
 * Description: Securely list your contact email addresses with the strongest protection against harvesters and scrapers. Go to the WebEmailProtector <a href="options-general.php?page=webemailprotector_plugin_options.php">Settings</a> menu to configure.
 * Version: 1.1.3
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
require_once(plugin_dir_path(__FILE__).'admin/webemailprotector_emo_unvalidate.php'); //validates entry
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
  // reset database during dev only (can remove later) or on first instantiation create 5 blanks
  $wep_reset_db = false;
  // any emails stored?
  $wep_current_user = wp_get_current_user();
  $wep_current_user_email = $wep_current_user->user_email;
  //set up version ver
  $wep_ver='v1.1.3';
  $wep_init = false;
  if ( get_option('wepdb_wep_ver') == true ) {
   if (get_option('wepdb_wep_ver') != $wep_ver){
     $wep_ver_old=get_option('wepdb_wep_ver');
     $wep_reason='upgrade from '.$wep_ver_old;
	 update_option('wepdb_wep_ver',$wep_ver);
	 $wep_init = true;}
  }
  else { 
   if ( get_option('wepdb_nuemails') == true ) {
    $wep_reason='upgrade from pre v1.1.1';}
   else {
    $wep_reason='new install';}	
   add_option('wepdb_wep_ver',$wep_ver);
   $wep_init = true;
  }
  //log the fact that a new version has been initialised
  if ( $wep_init == true ) {
   echo '<script type="text/javascript">';
   echo 'webemailprotector_emo_init("'.$wep_current_user_email.'","'.$wep_ver.'","'.$wep_reason.'");';
   echo '</script>';
   }
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
	add_option('wepdb_wep_email_'.$i,'your email address '.$i);
    if ( get_option('wepdb_wep_emo_'.$i) == true ) { delete_option('wepdb_wep_emo_'.$i) ; }
	add_option('wepdb_wep_emo_'.$i,'xxxx-xxxx-xxxx-xxxx-xxxx');
    if ( get_option('wepdb_wep_display_name_'.$i) == true ) { delete_option('wepdb_wep_display_name_'.$i) ; }
    add_option('wepdb_wep_display_name_'.$i,'your web text '.$i);
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
  echo '<h1><blue>Web</blue><green>Email</green><red>Protector</red> &nbsp;&nbsp;'.$wep_ver.'&nbsp;&nbsp;&nbsp;WordPress Plugin Settings Menu<br>';
  echo '<p style="font-size:12px;margin-top:0;margin-left:0;"><i>&nbsp;&nbsp;securing your web-site email addresses</i></p>';
  echo '</h1>';
  echo '<p>Enter the email addresses that you wish to secure into the <b>secured email address</b> column ';
  echo '(<i> these must be existing email <br>addresses that you will need to registered with us</i> ).</p>';
  echo '<p>Next enter the associated display text into the <b>displayed text</b> column (<i> this is the link text that will appear in place of the ';
  echo 'email <br>address when your WordPress pages are published</i> ).</p>';
  echo '<p>Then follow the further instructions below to register, validate and use each email.</p>';
  echo '<form action="" name="wep_settings" method="POST">';
  echo '<table id="wep_table"><tbody>';
  echo '<tr>';
  echo '<th colspan="3">secured email address </th>';
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
   echo '<td style="font-size:30px;padding-bottom:10px;">[</td>';
   echo '<td><input type="text" id="wep_emailtxt_'.$i.'" style="'.$color.';" onkeyup="webemailprotector_email_change(\''.$i.'\',this.value)" name="wep_email_'.$i.'" value="'.$emo_email.'"></td>';
   echo '<td style="font-size:30px;padding-bottom:10px;">]</td>';
   echo '<td><input type="text" id="wep_displaytxt_'.$i.'" onkeyup="webemailprotector_displayname_change(\''.$i.'\',this.value)" name="wep_name_'.$i.'" value="'.$display_name.'"></td>';
   echo '<td><input id="wep_validate_'.$i.'" type="button" class="button add another" value="validate" onclick="webemailprotector_validate(\''.$i.'\')"></td>';
   echo '<td><input id="wep_delete_'.$i.'" type="button" class="button add another" value="delete" onclick="webemailprotector_emo_delete(\''.$i.'\')"></td>';
   echo '</tr>';
  }
  echo '</tbody>';
  echo '<tr><td></td><td><green>green=validated</green>/<red>red=unvalidated</red></td></tr>';
  echo '<tr></tr>';
  echo '<tr><td></td><td>';  
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
  echo '<p><u>Registration and Validation Instructions:</u></p>';
  echo '<p>1. Each email address needs to be both registered with us and then validated in order to use.&nbsp;';
  echo '(<i> If you don\'t follow these 2 steps it will <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;not work! But luckily you should only ever have to do this once - even if we update the plugin</i> ).</p>';
  echo '<p>2. Firstly, to register each email address please follow the instructions at <a target="_blank" href="http://www.webemailprotector.com/register_wp.html">register</a>.';
  echo '&nbsp(<i> The email address must already exist and the';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;addressee will need to be able to receive to this email address in order to confirm their identity</i> ).</p>';
  echo '<p>3. Next, to validate that each registration succeeded and that it is ready to use click on the <input id="submit" type="button" class="button add another" value="validate"> button beside the email address.';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<i> You will be able to tell that the email address registration was successful ';
  echo 'because you get a pop-up confirmation message to say so and the<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;email text color will turn from red to green</i> ).</p>';
  echo '<p>4. Finally, to use simply place any of the above secured email addresses within square brackets ( <i>e.g. <b>[</b>email@yourdomain.com<b>]</b></i> )';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in your WordPress pages or widget text. (<i> You do not need to place within any "&#60;a&#62;" , "mailto" or other marked-up text</i> ).';
  echo '<p><u>Additional Notes:</u></p>';
  echo '<p>The <b>displayed text</b> column is for you to edit and set up as you like. The only excluded characters are \' and " . We strongly suggest that you ';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; do not use the email address itself as this will still leave you vulnerable. </p>';
  echo '<p>You can add additional email addresses using the <input id="submit" class="button add another" type="button" value="add another"> button.</p>';
  echo '<p>You can add delete any email addresses using the <input id="delete" class="button add another" type="button" value="delete"> button.</p>';
  echo '<p>As an option you can change the style of the email address appearance using CSS. For those familiar with CSS use the class <br>';
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"wep_email" of the &#60;a&#62; element using the selector a.wep_email {}.';
  echo '&nbsp;And look out for our new WebEmailProtector Styler - an email <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;stylise plugin- coming soon!</p>';
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
$newcontent=$content; //copy the page
if (is_page() and !is_admin()) {
 // populate dataset
 if ( get_option('wepdb_nuemails') == true){
  $wep_nuemails = get_option('wepdb_nuemails');
  for ($i = 1;$i <= $wep_nuemails; $i++) {
    $wep_email = get_option('wepdb_wep_email_'.$i);
    $wep_emo = get_option('wepdb_wep_emo_'.$i);
    $wep_display_name = get_option('wepdb_wep_display_name_'.$i);
	$wep_validated = get_option('wepdb_wep_validated_'.$i);
	if ($wep_validated == 'true'){
     $newtext='<a class="wep_email" href="JavaScript:emo(\''.$wep_emo.'\')" title="'.$wep_display_name.'">'.$wep_display_name.'</a>';
     //of form [email]
	 $newcontent = str_replace('['.$wep_email.']',$newtext,$newcontent); //email in brackets[]
	}
   }
  }
 }
return  $newcontent;
}

function webemailprotector_text_replace($widgettext) {
 $newwidgettext=$widgettext;
 if ( get_option('wepdb_nuemails') == true){
  $wep_nuemails = get_option('wepdb_nuemails');
  for ($i = 1;$i <= $wep_nuemails; $i++) {
    $wep_email = get_option('wepdb_wep_email_'.$i);
    $wep_emo = get_option('wepdb_wep_emo_'.$i);
    $wep_display_name = get_option('wepdb_wep_display_name_'.$i);
	$wep_validated = get_option('wepdb_wep_validated_'.$i);
	if ($wep_validated == 'true'){
     $newtext='<a class="wep_email" href="JavaScript:emo(\''.$wep_emo.'\')" title="'.$wep_display_name.'">'.$wep_display_name.'</a>';
     //of form [email]
	 $newwidgettext = str_replace('['.$wep_email.']',$newtext,$newwidgettext); //email in brackets[]
	}
  }
 }
 return $newwidgettext;
}
//do it in the pages
add_filter('the_content','webemailprotector_filter',100);
//do it in any widget text
add_filter('widget_text', 'webemailprotector_text_replace');

?>