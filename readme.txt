== occupied ==
Contributors: williamtaft
Tags: occupied, lock, custom
Requires at least: 4.8.1
Tested up to: 4.8.1
Requires PHP: 5.2.4
Stable tag: trunk

custom page locking for WordPress admin screens

== Screenshots ==

1. Demonstration

== Installation ==

put this plugin in the plugins directory of your wordpress installation

or

include the plugin in the root folder of your own plugin and require it with 

`require_once('occupied-installation-folder/occupied.php');`

== usage ==

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

== Changelog ==

* Initial upload

== todo ==

* break out styles
* add locking hooks 
* rethink using vue for a modal and some event handlers

== developers ==

This plugin is developed on [github](https://github.com/skinnyjames/occupied/)

== license ==

GPLv3
