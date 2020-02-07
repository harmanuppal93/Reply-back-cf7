<?php 
/**
*Plugin Name: Contact form listing
*Author: Webchefz
*Author URI: https://www.webchefz.com
*Description: This plugin managing the contacted user by CF7
*Plugin URI:  http://webchefz.com
Version: 0.1
*/

require 'mailerphp/PHPMailerAutoload.php';

wp_register_script( 'orderJSinfo', plugins_url('assests/script.js',__FILE__ ) );
wp_enqueue_script('orderJSinfo'); 


// Hide Admin bar for simple users on theme functionality
add_action('after_setup_theme', 'remove_admin_bar'); 
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
	  show_admin_bar(false);
	}
}


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once(ABSPATH.'wp-admin/includes/plugin.php');
	
	$plugin_data = get_plugin_data( __FILE__ );

// Admin hooks




function tennis_admin(){
	include 'inc/contact-form-order-listing.php';
}

function payment_listing(){
	include 'inc/contact-form-payment-listing.php';
}

add_action("admin_menu", "createMyMenus");

function createMyMenus() {
    add_menu_page("Lessons Panel", "Lessons Panel", 0, "CF7-contact-list", "tennis_admin");
    add_submenu_page("CF7-contact-list", "Lesson Payments", "Lesson Payments", 0, "CF7-payment-listing", "payment_listing");
}

function tennisUsers( $atts ) {
	
	return include 'inc/users.php';
}
add_shortcode( 'tennisUsers', 'tennisUsers' );


add_action( 'wp_ajax_create_contact_payment', 'prefix_ajax_create_contact_payment' );
function prefix_ajax_create_contact_payment() 
{
	global $wpdb;
    $tablename = $wpdb->prefix.'lesson_payments';
	session_start();
	if(isset($_REQUEST['contact_email']) && isset($_REQUEST['amount']) && isset($_REQUEST['CF7_id']) && isset($_REQUEST['contact_id']))
	{		
		$insert_data['contact_email'] = $_REQUEST['contact_email'];
		$insert_data['amount'] = $_REQUEST['amount'];
		$insert_data['message'] = $_REQUEST['message'];
		$insert_data['CF7_id'] = $_REQUEST['CF7_id'];
		$insert_data['CF7_name'] = $_REQUEST['CF7_name'];
		$insert_data['contact_id'] = $_REQUEST['contact_id'];
		$insert_data['contact_name'] = $_REQUEST['contact_name'];
		$insert_data['payment_status'] = 'pending';
		
		if($wpdb->insert( $tablename,$insert_data))
		{
			$insert_id = base64_encode($wpdb->insert_id);
			$share_url = site_url()."/lesson-payment?pay=$insert_id";
			
			
			$insert_data['message'] = str_replace("{username}", $_REQUEST['contact_name'] , $_REQUEST['message']);
			$insert_data['message'] = str_replace("{payment_link}", $share_url , $insert_data['message']);
			
			$to = $insert_data['contact_email'];
			
			$subject = "Tennis Lesson Payment";
			$headers[] = 'From: UAE Sports <adm.uaesports@gmail.com>';
			$headers[] = 'Reply-T: adm.uaesports@gmail.com';
			
			$message = $insert_data['message'];

			$sent = wp_mail($to, $subject, strip_tags($message), $headers);
			if($sent) 
			{				
				header("Location: ".site_url()."/wp-admin/admin.php?page=CF7-contact-list&success=1");
			}
			else
			{
				header("Location: ".site_url()."/wp-admin/admin.php?page=CF7-contact-list&error=1");
			}
			
			$_SESSION['wbc_message'] =  true;
		}
	
	}
	die();
	
	
}