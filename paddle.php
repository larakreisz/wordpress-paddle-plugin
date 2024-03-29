<?php

/*
 * The plugin bootstrap file
 * 
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                https://github.com/larakreisz/wordpress-paddle-plugin
 * @since               February 28, 2022
 * @package             WordPress Paddle Plugin
 * 
 * @wordpress-plugin
 * Plugin Name:         WordPress Paddle Plugin
 * Plugin URI:          https://github.com/larakreisz/wordpress-paddle-plugin
 * Description:         The plugin connects WordPress and Paddle and allows you to perform custom action after payment.
 * Version:             1.0
 * Author:              Lara Kreisz
 * Author URI:          https://github.com/larakreisz
 */
if (!defined('WPINC'))
    die;

/**
 * Define plugin name to use as global inside the plugin files
 * Rename this for plugin and update it as you required to change the plugin name for new versions.
 */


// 1. Enqueue Paddle Checkout
 
 add_action( 'wp_enqueue_scripts', 'enqueue_paddle_scripts' );
 function enqueue_paddle_scripts() {
    wp_register_script( 'paddle-js', 'https://cdn.paddle.com/paddle/paddle.js', null, null, true );
    wp_enqueue_script( 'paddle-js' );
 }
 


 // 2. Connect Paddle and WordPress
define('LARA_CONNECTOR_NAMESPACE', "lara-connector/v1");

add_action('rest_api_init', 'register_routes');
function register_routes() {
register_rest_route(LARA_CONNECTOR_NAMESPACE, '/paddlewebhooks/task', array(
   'methods' => 'POST',
   'callback' => 'paddlewebhooks_task_action',
   'permission_callback' => '__return_true'
    ));
}


function paddlewebhooks_task_action() {

$fields = $_POST;

if (isset($fields["passthrough"]) && !empty($fields["passthrough"])) {

// alle Variablen, die man so später braucht einsammeln
$passthrough = json_decode(stripslashes($fields['passthrough']), true);
$locationID = $passthrough["reitanlage"];
$eventID = $passthrough["veranstaltung"];
$product = $passthrough["art"];

// $email erst befüllen, wenn $fields["email"] nicht mehr leer ist. Das dauert einen Moment länger..
if (isset($fields["email"]) && !empty($fields["email"])) {
$email = $fields["email"];
}


// PRODUCT: PREMIUM EVENT (ONE-TIME-PAYMENT)
if ($product == 'PREMIUM-EVENT-EINMALZAHLUNG' && isset($eventID) && !empty($eventID)) {	
// Event upgraden auf Premium Event
update_post_meta($eventID, 'wpcf-rask-premium', '1');
}

// PRODUCT: PREMIUM EVENT (SUBSCRIPTION)
if ($product == 'PREMIUM-EVENT-ABONNEMENT' && isset($locationID) && !empty($locationID) && isset($eventID) && !empty($eventID)) {
// Event upgraden auf Premium Event
update_post_meta($eventID, 'wpcf-rask-premium', '1');
// Reitanlage auf auf Premium Event Abonnement updaten
update_post_meta($locationID, 'wpcf-cuteberry-premium-events-monatsabo', '1');
}	

}

	
	
   
echo json_encode([
        "code" => 200,
        "status" => "Success",
        "message" => "Meta data updated successfully.",
        "data" => $data
    ]);
    exit;	  
	

}
