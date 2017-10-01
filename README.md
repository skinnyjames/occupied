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

function register_my_plugin__menu(){
  add_menu_page("My Cool Plugin", "cool-plugin", "manage_options", "my_cool_plugin_page", "render_my_cool_plugin", "dashicons-heart", 7);
}

function render_my_cool_plugin(){
  // Occupied::protect enables locking on this page
  // and returns a lock array
  $lock = Occupied::protect();

  echo "<h1>My Cool Plugin!</h1>";
}
```

## todo

* break out styles
* add locking hooks 
* enable lock dialog customization
* rethink using vue for a modal and some event handlers

## developers

1. clone the repo
2. `npm install`
3. `npm run build` to generate the occupied.js file




