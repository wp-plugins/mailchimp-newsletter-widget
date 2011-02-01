<?php
/**
 * @package MailChimp Newsletter Widget
 * @version 1.0
 */
/*
Plugin Name: MailChimp Newsletter Widget
Plugin URI: http://matthewpoer.wordpress.com/mailchimp-news-widget-plugin/
Description: Integrate your MailChimp sign-up form into your WordPress dashboard. This plugin provides a custom widget containing a sign-up form, and tracks the newsletter sign-ups that the form receives. You get to know the email addresses that signed-up, which ones confirmed their sign-ups, and which pages on your site generate the most sign-ups.
Author: Matthew Poer
Version: 1.0
Author URI: http://matthewpoer.wordpress.com/
*/

function load_jquery(){
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-dialog');
	wp_register_style('jquery-css','http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/base/jquery-ui.css');
	wp_enqueue_style('jquery-css');
}
add_action('init','load_jquery');

function GenerateMCLink(){
	add_menu_page(		// top-level menu, placed under 1st Dashboard menu item
		'MailChimp Sign-Up Stats',		 // Page Title: the title of the page once you nav to it
		'MailChimp',		 // Menu Title: displayed in link/menu button 
		'manage_options',	  // Capability: who can use it? admin-role
		'MCMenu',			 // menu Slug: internal reference, dor submenu to reference 
		'MC_Main',			// Function: the function (in this script) that needs to be executed
		null,				 // Icon URL
		3					 // Array Position
	); 
	add_submenu_page(	// Submenu for MC Settings
		'MCMenu',			// Refers to a top-level menu group
		'Config MailChimp API Key',		// Page Title
		'Config MailChimp API Key',		// Display Text
		'manage_options',	// Capability
		'MCMenu_sub_settings',	// Unique Reference string
		'MC_Settings'			// Function to call
	);
	add_submenu_page(	// Submenu for configuration of sign-up form/widget
		'MCMenu',			// Refers to a top-level menu group
		'Config MailChimp Widget',		// Page Title
		'Config MailChimp Widget',		// Display Text
		'manage_options',	// Capability
		'MCMenu_sub_form',	// Unique Reference string
		'MC_Form'			// Function to call
	);
}

function MC_Main(){
	require_once('MCAPI.class.php');
	$mailchimp_apikey = get_option( 'mailchimp_apikey' );
	$mcapi = new MCAPI($mailchimp_apikey);
	$details = $mcapi->getAccountDetails();
	if (!(isset($mailchimp_apikey)) || $mailchimp_apikey = ''){
		$msg = "<h3 style='color:red'>You don't 
		have an Apikey configured, yet. Please visit 
		the <a href='admin.php?page=MCMenu_sub_settings'>settings page</a>.</h3>";
		echo $msg;
		break;
	}
	if ($mcapi->errorMessage){
		$msg = "<h3 style='color:red'>MailChimp Error</h3>";
		$msg .= "<p>$mcapi->errorMessage</p>\n";
		$msg .= "<p>You may want to visit the <a href='admin.php?page=MCMenu_sub_settings'>settings page</a>.</p>";
		echo $msg;
		break;	
	}
	$tablecss = <<<EOCSS
	<style type="text/css">
	table, table * {
	border:1px solid black;
	padding:3px;
	}
	</style>
EOCSS;
	echo $tablecss;
	$listid = get_option('mailchimp_listid');
	echo "<div class='wrap'>
		<div class='icon32' id='icon-index'><br></div>
		<h2>MailChimp Newsletter Sign-up Stats</h2>";
	
	echo "<h3>Sign-Up Stats: Number of Sign-Ups by Page</h3>\n";
	echo "<table class='table_element_with_borders'><tbody>\n";
	echo "<tr>	<th>Page URI</tg>	<th>Number Of Sign-Ups</th>	</tr>\n";
	$stats = get_option('mailchimp_signup_stats');
	foreach($stats as $URI => $Count){
		echo "\t<tr>	<td>$URI</td>	<td>$Count</td>	</tr>\n";
	}
	echo "</tbody></table>\n";

	echo "<h3>Newsletter List: Who Signed Up?</h3>\n";
	echo "<table class='table_element_with_borders'><tbody>\n";
	echo "<tr>	<th>Email Address</th>	<th>Confrimed?</th>	<th>Still Active on List?</th>	</tr>\n";
	$emaillog = get_option('mailchimp_email_log');
	foreach($emaillog as $email){
		echo "\t<tr>	<td>$email</td>";
		// did they confirm registration on the list? if not, they won't return at all
		$memberinfo = $mcapi->listMemberInfo($listid,$email);
		if($memberinfo['data'][0]['status'] == "pending"){
			echo "<td>No</td>	<td>-</td>	</tr>\n";
		} else {
			echo "<td>Yes</td>	<td>".$memberinfo['data'][0]['status']."</td>	</tr>\n";
		}
	}
	echo "</tbody></table>\n";
	
	echo "</div>"; // end the 'wrap' div
}

function MC_Settings(){
	echo <<<EOHTML
<div class='wrap'>
		<div class='icon32' id='icon-options-general'><br></div>
		<h2>Configure MailChimp API Key</h2>
EOHTML;
	if ( isset($_POST['mailchimp_apikey']) ) 
	{
		$mailchimp_apikey = $_POST['mailchimp_apikey'];
		update_option('mailchimp_apikey',$mailchimp_apikey);
		echo "<h3 style='color:red'>MailChimp API Key Saved, thanks!</h3>";
	}
	$javascript = <<<JAVASCRIPT
	<script type='text/javascript'>
	function showApikeyExplain(){
		document.getElementById('apikey_explain').style.display = 'block';
	}
	function getMailChimpAPIKey() {
		jQuery("#dialog_loading_msg").dialog({ 
			modal:true,
			title:'Please Wait', 
			resizable:false,
			draggable:false,
		});
		jQuery.ajax({
		  url: '../index.php?mailchimp_apikey='+document.getElementById('mailchimp_apikey').value,
		  success: function(data) {
		    jQuery('.result').html(data);
		    if(data != 'SUCCESS'){
			    jQuery("#dialog_loading_msg").dialog('close');
			    alert(data);
		    } else {
			    var newtext = "<p><img src='images/loading-publish.gif' alt='(loading icon) ' width='24px' height='24px' />	Key Verified, saving... </p>";
				jQuery("#dialog_loading_msg").html(newtext);
			    document.getElementById('mailchimp_settings').submit();		    
		    }
		  },
		  failure: function(data) {
				  jQuery("#dialog_loading_msg").dialog('close');
				alert("There was an error processing the API Key. Please contact an administrator if this issue persists.");			  
		  },
		});
				
	}
	</script>
	
JAVASCRIPT;
	echo $javascript;
	$mailchimp_apikey = get_option( 'mailchimp_apikey' );
	echo "<form name='mailchimp_setings' action='admin.php?page=MCMenu_sub_settings' method='POST' id='mailchimp_settings'>\n";
	echo "<p>MailChimp API Key<br />\n";
	echo "<input type='text' value='$mailchimp_apikey' name='mailchimp_apikey' id='mailchimp_apikey' />\n";
	echo "<span style='font-size: 1.17em;font-weight: bold;margin: 1em 1em;' 
	onclick='showApikeyExplain();'>What's this?</span></p>\n";	
	echo "<div id='apikey_explain' style='display:none;width:500px;'>MailChimp.com provides users with
	<strong>API Keys</strong> to give to 3rd party applications (like me) so that your actual account credentials (Username 
	and Password) never have to be shared. You can read more about API Keys (including where to
	get one) in the <a target='_blank' href='http://www.mailchimp.com/kb/article/where-can-i-find-my-api-key'>MailChimp 
	Knowledge Base article &quot;Where can I find my API Key?&quot;</a></div>\n";
	echo "<p><input type='button' value='Save API Key' onclick='getMailChimpAPIKey();' id='apikeyfetchbutton' /></p>\n";
	echo "<div id='dialog_loading_msg' style='display:none;'>
	<p><img src='images/loading-publish.gif' alt='(loading icon) ' width='24px' height='24px' />
	Verifing MailChimp API Key, please wait... </p>
	</div>";
	echo "</div>"; // end the 'wrap' div
}
function MC_Form(){
	echo <<<EOHTML
<div class='wrap'>
		<div class='icon32' id='icon-link-manager'><br></div>
		<h2>Configure Sign-Up Form Widget</h2>
EOHTML;
	if(isset($_POST['list_id'])){
		$listid = $_POST['list_id'];
		update_option('mailchimp_listid',$listid);
		$form = $_POST['mailchimp_widget_form'];
		update_option('mailchimp_custom_form',$form);
		$msg = "<h3 style='color:red'>Widget Configuration Saved, thanks!</h3>\n";
		echo $msg;
	}
	require_once('MCAPI.class.php');
	$mailchimp_apikey = get_option( 'mailchimp_apikey' );
	$mcapi = new MCAPI($mailchimp_apikey);
	$lists = $mcapi->lists();
	if (!(isset($mailchimp_apikey)) || $mailchimp_apikey = ''){
		$msg = "<h3 style='color:red'>You don't 
		have an Apikey configured, yet. Please visit 
		the <a href='admin.php?page=MCMenu_sub_settings'>settings page</a>.</h3>";
		echo $msg;
		break;
	}
	if ($mcapi->errorMessage){
		$msg = "<h3 style='color:red'>MailChimp Error</h3>";
		$msg .= "<p>$mcapi->errorMessage</p>\n";
		$msg .= "<p>You may want to visit the <a href='admin.php?page=MCMenu_sub_settings'>settings page</a>.</p>";
		echo $msg;
		break;	
	}
	echo "<div id='new_form'>\n";
	echo "<form id='new_mailchimp_sign_up_form' name='new_mailchimp_sign_up_form' method='POST' action='admin.php?page=MCMenu_sub_form'>\n";
	echo "<p>MailChimp Lists &mdash; Which list do you want the widget sign-ups to be added to?</p>\n";
	echo "<p><select id='list_id' name='list_id'>\n";
	foreach ($lists['data'] as $list){
		echo "\t<option value='".$list['id']."'>".$list['name']."</option>\n";
	}
	echo "</select></p>";
	echo "<p>Widget HTML Markup &mdash; Customize the widget to better match your site. This is optional. If you choose to custmoize your widget, be sure to <strong>leave the input fields with the apprpriate ID and NAME attributes.</strong></p>\n";
	echo "<p><textarea name='mailchimp_widget_form' id='mailchimp_widget_form' style='width:80%;height:300px;'>";

	$customWidgetForm = get_option( 'mailchimp_custom_form' );
	$defaultWidgetForm = <<<EOFORM
	<li id='mailchimp_signup' class='widget-container widget_meta'>
	<h3 class='widget-title'>Newsletter Sign-up</h3>
	<p>First Name<br />
	<label for='first_name' class='screen-reader-text'>First name</label>
	<input type='text' name='mc_first_name' id='mc_first_name' /></p>
	<p>Last Name<br />
	<label for='last_name' class='screen-reader-text'>Last name</label>
	<input type='text' name='mc_last_name' id='mc_last_name' /></p>
	<p>Email Address<br />
	<label for='email_addr' class='screen-reader-text'>Email Address</label>
	<input type='text' name='mc_email_addr' id='mc_email_addr' /></p>
	<p><input type='submit' value='Sign up' /></p>
	</li>
EOFORM;
	if (isset($customWidgetForm) && $customWidgetForm != '' && $customWidgetForm != FALSE){
		echo stripslashes($customWidgetForm); // ensure we remove un-needed slashing from database save
	} else {
		echo $defaultWidgetForm;
	}
	echo "</textarea></p>\n";
	$ResetFormJavaScript = <<<EOJS
	<script type='text/javascript'>
	function ResetWidget(){
		if (confirm("Do you really want to reset the widget form? Your current widget will be lost.")) { 
		var defaulttext = "<li id='mailchimp_signup' class='widget-container widget_meta'>\\n" +
	"<h3 class='widget-title'>Newsletter Sign-up</h3>\\n" +
	"<p>First Name<br />\\n" +
	"<label for='first_name' class='screen-reader-text'>First name</label>\\n" +
	"<input type='text' name='mc_first_name' id='mc_first_name' /></p>\\n" +
	"<p>Last Name<br />\\n" +
	"<label for='last_name' class='screen-reader-text'>Last name</label>\\n" +
	"<input type='text' name='mc_last_name' id='mc_last_name' /></p>\\n" +
	"<p>Email Address<br />\\n" +
	"<label for='email_addr' class='screen-reader-text'>Email Address</label>\\n" +
	"<input type='text' name='mc_email_addr' id='mc_email_addr' /></p>\\n" +
	"<p><input type='submit' value='Sign up' /></p>\\n" +
	"</li>";
		document.getElementById('mailchimp_widget_form').value = defaulttext;
		}
	}
	</script>
	<p><input type='button' value='Reset Widget HTML' onclick='ResetWidget()' /></p>
EOJS;
	echo $ResetFormJavaScript;
	echo "<p><input type='submit' value='Save Sign-Up Widget' /></p>\n";
	echo "</form>\n</div>";
	echo "</div>"; // end the 'wrap' div
}

add_action('admin_menu', 'GenerateMCLink');

function DisplayWidget() {
	echo "<form name='mailchimp_sign_up_form' id=='mailchimp_sign_up_form' action='index.php' method='POST'>";
	echo "<input type='hidden' name='mc_this_page' id='mc_this_page' value='$_SERVER[REQUEST_URI]' />";
	$customWidgetForm = get_option( 'mailchimp_custom_form' );
	$defaultWidgetForm = <<<EOFORM
	<li id='mailchimp_signup' class='widget-container widget_meta'>
	<h3 class='widget-title'>Newsletter Sign-up</h3>
	<p>First Name<br />
	<label for='first_name' class='screen-reader-text'>First name</label>
	<input type='text' name='mc_first_name' id='mc_first_name' /></p>
	<p>Last Name<br />
	<label for='last_name' class='screen-reader-text'>Last name</label>
	<input type='text' name='mc_last_name' id='mc_last_name' /></p>
	<p>Email Address<br />
	<label for='email_addr' class='screen-reader-text'>Email Address</label>
	<input type='text' name='mc_email_addr' id='mc_email_addr' /></p>
	<p><input type='submit' value='Sign up' /></p>
	</li>
EOFORM;
	if (isset($customWidgetForm) && $customWidgetForm != '' && $customWidgetForm != FALSE){
		echo stripslashes($customWidgetForm); // ensure we remove un-needed slashing from database save
	} else {
		echo $defaultWidgetForm;
	}
	echo "</form>";
}
 
function DisplayWidget_init()
{
  register_sidebar_widget(__('Mailchimp Sign-Up Form'), 'DisplayWidget');
}
add_action("plugins_loaded", "DisplayWidget_init");

function NewsletterSignUp(){
	if(isset($_POST['mc_email_addr'])){
		require_once('MCAPI.class.php');
		$mailchimp_apikey = get_option( 'mailchimp_apikey' );
		$mailchimp_listid = get_option( 'mailchimp_listid' );
		$mcapi = new MCAPI($mailchimp_apikey);
		// error checking
		$details = $mcapi->lists();
		if (!(isset($mailchimp_apikey)) || $mailchimp_apikey = ''){
			$msg = "<h3 style='color:red'>You don't 
			have an Apikey configured, yet. Please visit 
			the <a href='wp-admin/admin.php?page=MCMenu_sub_settings'>settings page</a>.</h3>";
			echo $msg;
			break;
		}
		if (!(isset($mailchimp_listid)) || $mailchimp_listid == '' || $mailchimp_listid == FALSE){
			$msg = "<h3 style='color:red'>You don't 
			have an mailing list configured for newsletters, yet. Please visit 
			the <a href='wp-admin/admin.php?page=MCMenu_sub_form'>newsletter page</a>.</h3>";
			echo $msg;
			break;
		}
		if ($mcapi->errorMessage){
			$msg = "<h3 style='color:red'>MailChimp Error</h3>";
			$msg .= "<p>$mcapi->errorMessage</p>\n";
			$msg .= "<p>You may want to visit the <a href='wp-admin/admin.php?page=MCMenu_sub_settings'>settings page</a>.</p>";
			echo $msg;
			break;	
		}
		// set dev-friendly var names
		$email = $_POST['mc_email_addr'];
		$fname = $_POST['mc_first_name'];
		$lname = $_POST['mc_last_name'];
		$apage = $_POST['mc_this_page'];
		$merge_vars = array(
			'fname' => $fname,
			'lname' => $lname,
		);
		// try the subscribe if vars are in place
		if ( isset($email) && isset($fname) && isset($lname) && isset($apage) && $email != '' && $fname != '' && $lname != '' && $apage != ''){
			$mcapi->listSubscribe($mailchimp_listid, $email, $merge_vars, 'html', true, true, true, true);
			if ($mcapi->errorMessage){
				$admin_email = get_settings('admin_email');
				$msg = "<h3 style='color:red'>Newsletter Error</h3>";
				$msg .= "There was an error registering your email address for our newsletter:";
				$msg .= "<p>$mcapi->errorMessage</p>\n";
				$msg .= "<p>Please try registering again. If this error persists, please contact the <a href='mailto:$admin_email'>site administrator</a></p>";
				echo $msg;
				break;	
			} else {
				// if we made it this far, the subscribe was good and we want record things
				$stats = get_option ('mailchimp_signup_stats');	// get stats
				$stats[$apage]++; // update stats
				update_option('mailchimp_signup_stats',$stats); // save stats
				$emaillog = get_option('mailchimp_email_log'); // get email list
				$emaillog[] = $email; // update email list
				update_option('mailchimp_email_log',$emaillog); // save email list
				
				// let the visitor know that everything's done
				echo "Thank you for signing up for our newsletter. Please check your email inbox for a confirmation to join the list.</p>
				<p>You should be automatically redirected back to the site. If you are not redirected in 5 seconds, <a href='javascript:history.go(-1);'>click here</a></p>";
				echo "<script type='text/javascript'>
				setTimeout('history.go(-1)',3000);
				</script>";
				
				// stop loading WP stuff
				die(); 
			}
		} else {
			$msg = "<h3 style='color:red'>Newsletter Error</h3>";
			$msg .= "<p>There was an error registering your information for our newsletter. You did not provide:<ul>";
			if (!isset($email)){ $msg .= "<li>Email Address</li>"; }
			if (!isset($fname)){ $msg .= "<li>First Name</li>"; }
			if (!isset($lname)){ $msg .= "<li>Last Name</li>"; }
			$msg .= "</ul></p>";
			$msg .= "<p>Please try registering again. If this error persists, please contact the <a href='mailto:$admin_email'>site administrator</a></p>";
			echo $msg;		
		} // end subscribe attempts + error checking
	}
}

add_action('init','NewsletterSignUp');

function MCAPI_KeyAuth(){
	if(isset($_GET['mailchimp_apikey'])){	
		require_once('MCAPI.class.php');
		$mcapi = new MCAPI($_GET['mailchimp_apikey']);
		$mcapi->getAccountDetails();
		if(!($mcapi->errorMessage)){
			echo "SUCCESS";
		} else {
			echo "ERROR: " . $mcapi->errorMessage;
		}
		die(); // stop the script from continuing to load WP stuff
	}
}

add_action('init','MCAPI_KeyAuth');

?>
