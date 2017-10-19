<?php 
/* 
Plugin Name: occupied
Description: custom page locking for WordPress admin pages
Version: 0.1.0
Author: skinnyjames
License: GPLv3
*/

require_once('occupied/class.occupied.php');
Occupied::register_hooks();

?>
