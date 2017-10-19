occupied
=========

custom page locking for WordPress admin screens

## installation

put this plugin in the plugins directory of your wordpress installation

or

include the plugin in the root folder of your own plugin and require it with 

`require_once('occupied-installation-folder/occupied.php');`

## usage

In the function for your custom page, just use `Occupied::protect()` to enable locking on that page.

```php 
add_action('admin_menu', 'register_my_plugin_menu');

function register_my_plugin_menu(){
  add_menu_page("My Cool Plugin", "cool-plugin", "manage_options", "my_cool_plugin_page", "render_my_cool_plugin", "dashicons-heart", 7);
}

function render_my_cool_plugin(){
  // Occupied::protect enables locking on this page
  // and returns a lock array
  // optional: pass a message to the protect method to appear on the lock modal.
  $lock = Occupied::protect("Cool Plugin Occupied!");

  echo "<h1>My Cool Plugin!</h1>";
}
```
In another action, you can check whether a screen is occupied by the current user

```php
function some_other_action(){
  if(Occupied::is_authorized('toplevel_page_my_cool_plugin_page')){
    // business logic, save to database, etc..
  }else{
    // return error
  }
}
```
check it
========= 

![](screenshots/occupied_screenshot.png?raw=true "screenshot")

todo
=====

* break out styles
* add locking hooks 
* ~~enable lock dialog customization~~
* rethink using vue for a modal and some event handlers

developers
===========

1. clone the repo
2. `cd occupied && npm install & npm run build`

warnings
==========

this project is currently in development.
feedback and pull requests welcome!

license
========

GPLv3


