<?php 
/* 
Plugin Name: occupied
Description: custom page locking for WordPress admin pages
Version: 0.1.0
Author: skinnyjames
*/

require_once('occupied/class.occupied.php');

Occupied::register_hooks();

/* Demo */
add_action('admin_menu', 'occupied_register_test_menu');

function occupied_register_test_menu(){
  add_menu_page("Occupied Test","occupied-test","manage_options", "occupied_test_page", "occupied_test_menu", "dashicons-heart",7);
}

function occupied_test_menu(){
  $lock = Occupied::protect();

  echo "<h1>Hello World</h1>";
}

?>
