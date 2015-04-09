<?php
/*
 * Plugin Name: WebEmailProtector
 * Plugin URI: http://www.webemailprotector.com
 * Description: Safely add your contact email addresses on your WordPress website with the best protection against spammers. Go to the WebEmailProtector <a href="options-general.php?page=webemailprotector_plugin_options.php">Settings</a> menu to configure.
 * Version: 1.4.0
 * Author: David Srodzinski
 * Author URI: http://www.webemailprotector.com/about.html
 * License: GPL2
*/

/*  Copyright 2013-2015 DAVID SRODZINSKI WEBEMAILPROTECTOR  (email : david@webemailprotector.com)

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

function webemailprotector_emailstyle() {
	$cssurl = plugin_dir_url(__FILE__).'css/webemailprotector_emailstyle.css';
    wp_enqueue_style('wep-theme1', $cssurl);
}
add_action('wp_enqueue_scripts', 'webemailprotector_emailstyle');

function webemailprotector_youremailstyle() {
	$cssurl = plugin_dir_URL(__FILE__).'css/webemailprotector_youremailstyle.css';
    wp_enqueue_style('wep-theme2', $cssurl);
}

if (!file_exists(plugin_dir_path(__FILE__).'css/webemailprotector_youremailstyle.css')) {
$data='/* PUT YOUR SPECIFIC EMAIL FORMATTING HERE*/'.PHP_EOL.PHP_EOL.'a.wep_email {}'.PHP_EOL.PHP_EOL.'a.wep_email:hover {}'.PHP_EOL;
file_put_contents(plugin_dir_path(__FILE__).'css/webemailprotector_youremailstyle.css' , $data);
}

add_action('wp_enqueue_scripts', 'webemailprotector_youremailstyle');

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

// function triggered when plugin activated
// but does not work as does not like the echos ..... maybe should do an enque script
function webemailprotector_activate() {
  $wep_current_user = wp_get_current_user();
  $wep_current_user_email = $wep_current_user->user_email;   
  //echo '<script type="text/javascript">';
  //echo 'webemailprotector_emo_act("'.$wep_current_user_email.'");';
  //echo '</script>';
  //echo $wep_current_user_email;
  $scripturl = plugin_dir_url(__FILE__).'scripts/webemailprotector_initscripts.js';
  wp_enqueue_script('webemailprotector_initscript',$scripturl,array('jquery'));
}
register_activation_hook( __FILE__, 'webemailprotector_activate' );

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
  $wep_ver='v1.4.0';
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
    add_option('wepdb_wep_display_name_'.$i,'your display text '.$i);
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
  echo '<br />';
  echo '<br />';
  echo '<img style="display:inline;margin:0px 0px 0px 60px;vertical-align:middle" src="'.plugin_dir_url(__FILE__).'images/webemailprotector_logo.png" width="398px" height="102px"/>';
  echo '<h1 style="display:inline;margin:0px 0px 0px 0px;">&nbsp;&nbsp;'.$wep_ver.'&nbsp;&nbsp;&nbsp;WordPress Plugin Settings Menu</h1>';
  echo '<table class="wep_top"><tbody><tr>';
  echo '<td style="width:80px;"></td>';
  echo '<td>Enter the email addresses that you wish to secure into the <b>secured email address</b> column of the table below :';
  echo '<br /><br />(<i> these must be existing email addresses that you will need to register with us as described under the table</i> ).</td>';
  echo '<td style="width:10px;"></td>';
  echo '<td>Next enter the associated display text into the <b>displayed text</b> column : <br /><br /><br />(<i> this is the link text that will appear in place of the ';
  echo 'email address when your WordPress pages are published</i> ).</td>';
  echo '<td style="width:10px;"></td>';
  echo '<td style="width:200px;">Then follow the further instructions below the table to register, validate and use each email entity.</td>';
  echo '</tr></tbody></table>';
  echo '<form action="" name="wep_settings" method="POST">';
  echo '<table id="wep_table" class="wep_main"><tbody>';
  echo '<tr>';
  echo '<th colspan="3">secured email address </th>';
  echo '<th>displayed text</th>';
  echo '<th colspan="3">actions</th>';
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
   echo '<td><input id="wep_register_'.$i.'" type="button" class="button add another" value="register" onclick="window.open(\'http://www.webemailprotector.com/cgi-bin/reg.py?cms=wp&email='.$emo_email.'\')"></td>';
   echo '<td><input id="wep_validate_'.$i.'" type="button" class="button add another" value="validate" onclick="webemailprotector_validate(\''.$i.'\',\''.$wep_current_user_email.'\')"></td>';
   echo '<td><input id="wep_delete_'.$i.'" type="button" class="button add another" value="delete" onclick="webemailprotector_emo_delete(\''.$i.'\')"></td>';
   echo '</tr>';
  }
  echo '</tbody>';
  echo '<tr><td></td><td><green>green=validated</green>/<red>red=unvalidated</red></td></tr>';
  echo '<tr></tr>';
  echo '<tr><td></td><td>';  
  echo '<input id="submit" class="button add another" type="button" value="add another" onclick="webemailprotector_emo_new()">';
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
  echo '<p>1. Each email address needs to be both registered and then validated with us in order to use.&nbsp;';
  echo '(<i> If you don\'t follow these 2 steps it will <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;not work! But luckily you should only ever have to do this once per email - even if we update the plugin</i> ).</p>';
  echo '<p>2. Firstly, to register each email address with us click on the <input id="submit" type="button" class="button add another" value="register"> button beside the email address.';
  echo '&nbsp(<i> This places a copy of your address';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;on our server. The address must exist as the addressee will need to be able to receive messages to this email in order to confirm their identity</i> ).</p>';
  echo '<p>3. Next, to validate that each registration succeeded and that it is ready to use click on the <input id="submit" type="button" class="button add another" value="validate"> button beside the email address.';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<i> You will be able to tell that the email address registration was successful ';
  echo 'because you get a pop-up confirmation message to say so and the<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;email text color will turn from red to green</i> ).</p>';
  echo '<p>4. Finally, to use simply place any of the secured email addresses as a shortcode ie within square brackets on your WordPress pages,';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; menus or widget text <i>e.g. <b>[</b>email@yourdomain.com<b>]</b></i>. (<i> You do not need to place within any "&#60;a&#62;" , "mailto" or other marked-up text</i> ).';
  echo '<p>5. In addition to shortcodes, the plugin from v 1.1.6 now also replaces all links that contain your secured email address of the forms ';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;mailto:[email@yourdomain.com] or mailto:email@yourdomain.com. This was a change post v1.1.6 in order to support social icons';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;and other links within menus of WP themes at the request of users.</br />'; 
  echo '<p><u>Additional Notes:</u></p>';
  echo '<p>The <b>displayed text</b> column is for you to edit and set up as you like. The only excluded characters are \' and " . We strongly suggest that you ';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; do not use the email address itself as this will still leave you vulnerable. </p>';
  echo '<p>You can add additional email addresses using the <input id="submit" class="button add another" type="button" value="add another"> button.</p>';
  echo '<p>You can add delete any email addresses using the <input id="delete" class="button add another" type="button" value="delete"> button.</p>';
  echo '<p>As an option you can change the style of the email address appearance using CSS. For those familiar with CSS use the class "wep_email" of<br>';
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;the &#60;a&#62; element using the selector a.wep_email {}.';
  echo '&nbsp;A template css file is provided for you to edit the style as you wish.';
  echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;It can be located within the plugin directory at : webemailprotector/css/webemailprotector_youremailstyle.css.';
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
//function to do a basic text replace, could make more advanced with preg or work on links only but not sure why as would be slower as
//i think would need to access the db at multipoints along the way if so
function webemailprotector_text_replace($text) {
 $newtext=$text;
 if ( get_option('wepdb_nuemails') == true){
  $wep_nuemails = get_option('wepdb_nuemails');
  for ($i = 1;$i <= $wep_nuemails; $i++) {
    $wep_email = get_option('wepdb_wep_email_'.$i);
    $wep_emo = get_option('wepdb_wep_emo_'.$i);
    $wep_display_name = get_option('wepdb_wep_display_name_'.$i);
	$wep_validated = get_option('wepdb_wep_validated_'.$i);
	if ($wep_validated == 'true'){
	 //of form mailto:	
     $newswaptext='JavaScript:emo(\''.$wep_emo.'\')';
	 $newtext = str_replace('mailto:'.$wep_email,$newswaptext,$newtext);
	 //of form mailto:[email] - just in case added this way
     $newswaptext='JavaScript:emo(\''.$wep_emo.'\')';
	 $newtext = str_replace('mailto:['.$wep_email.']',$newswaptext,$newtext);	 
     //of form [email]
	 $newswaptext='<a class="wep_email" href="JavaScript:emo(\''.$wep_emo.'\')" title="'.$wep_display_name.'">'.$wep_display_name.'</a>';
	 $newtext = str_replace('['.$wep_email.']',$newswaptext,$newtext);
	}
  }
 }
 return $newtext;
}
// it in the pages
add_filter('the_content', 'webemailprotector_text_replace',100);
//do it in any widget text
add_filter('widget_text', 'webemailprotector_text_replace',100);
//do it in the menus
add_filter( 'wp_nav_menu', 'webemailprotector_text_replace',100);
?>