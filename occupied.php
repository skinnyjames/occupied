<?php 
/* 
Plugin Name: occupied
Plugin URI: https://github.com/skinnyjames/occupied
Github Plugin URI: skinnyjames/occupied
Description: custom page locking for WordPress admin pages
Version: 0.1.0
Author: williamtaft
Author URI: https://github.com/skinnyjames
License: GPLv3
*/

require_once('occupied/class.occupied.php');

// registers the plugin
Occupied::register_hooks();

// cleanup on uninstall
register_uninstall_hook(__FILE__, 'occupied_uninstall');

function occupied_uninstall(){
  delete_option(Occupied::WP_OPTIONS_NAME);
}

?>
