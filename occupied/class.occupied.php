<?php 

class Occupied {
  // Should store object in options table
  const WP_OPTIONS_NAME = 'occupied_lock'; 
  const SESSION_LENGTH = "+5 minutes";



  public static function register_hooks(){
    //The rest
    add_action( 'current_screen', 'Occupied::protect_screen' ); 
    add_action('wp_ajax_occupied_take_over', 'Occupied::take_over');  
    add_filter('heartbeat_received', 'Occupied::heartbeat_received', 10, 2);
  }
  
  public static function heartbeat_received($response, $data){
    if ( !isset( $data["occupied_data"]["screen"]) ){
      $response["test"] = "hello";
      return $response;
    }else{
      $screen = $data["occupied_data"]["screen"]; 
      $response["occupied_lock"] = self::padlock_generate($screen);
      return $response;
    } 
  }

  public static function take_over(){
    $screen_id = $_POST['screen'];
    $lock = self::padlock_generate($screen_id, true);
    echo json_encode($lock);
    wp_die();
  }
  //TODO: implement redirect

  //TODO: use page load hooks
  // Filter that runssss
  public static function protect_screen(){
    $current_screen = get_current_screen();
    $current_user = wp_get_current_user();
    if ($current_user && $current_screen){
      $lock = self::padlock_get($current_screen->id);
      if ($lock) {
        // enqueue styles and register lock modal
        add_action('admin_enqueue_scripts', 'Occupied::register_scripts');
        add_action('admin_footer', 'Occupied::register_padlock' );
      }
    }
  }  

  public static function register_scripts(){
    wp_enqueue_script('occupied', plugins_url('/js/occupied.js', __FILE__), array('jquery'));
  }
  // Enqeue scripts, styles, echo 
  // TODO: don't use vue?
  public static function register_padlock(){

    add_action( 'current_screen', 'Occupied::protect_screen' ); 

    $current_screen = get_current_screen();
    $lock = self::padlock_generate($current_screen->id);  
    $json_lock = json_encode($lock);
    $app_el = "occupied-lock-dialog";
    $screen_id = $current_screen->id;

    // Make redirect url
    global $wp;
    $current_url = add_query_arg( $_SERVER['QUERY_STRING'], '', admin_url( 'admin.php' ));
    $referer = wp_get_referer();
    if (!$referer || $referer == $current_url){
      $referer = get_admin_url();
    }
    // Output Vue App
    $output = <<<HTML
    <div id='$app_el'>
      <template v-if="lock.authenticated === false">
        <div class="occupied-dialog-mask">
          <div class="occupied-dialog-wrapper">
            <div class="occupied-dialog-container">
              <div style="display:flex;flex-direction:column;min-height:10vh;justify-content:space-between;">
                <div class="avatar-and-body" style="display:flex;align-items:flex-start;">
                  <img :src="lock.owner_avatar_url" style="width:96px;height:96px;"/>
                  <div class="occupied-dialog-text" style="margin-left:1em;">
                    <h3 style="margin-bottom:0;">We are so, so, sorry</h3> 
                    <p style="margin-top:0;">
                      <span style="font-size:larger;font-weight:bold;">{{lock.owner_display_name}}</span> is currently editing this page
                      <br>If you take over, {{lock.owner_display_name}} will be locked out of editing this page.
                    </p>
                  </div>
                </div>
                <div class="occupied-modal-actions" style="display:flex;justify-content: space-between;">
                  <a v-on:click.prevent="go_back" class="button" href="#">Leave</a>
                  <a v-on:click.prevent="take_over" class="button button-primary" href="#">Take Over</a>
                </div> 
              </div>   
            </div>
          </div>
        </div>  
      </template>
    </div>  
    <style>
      .occupied-dialog-mask {
        position: fixed;
        top: 0;
        z-index: 99998;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,.5);
        display: flex;
        justify-content: center;
        transition: opacity .3s ease;
      }
      .occupied-dialog-wrapper {
        /* = Why support this bad behavior?
         * display: table-cell
         * vertical-align: middle;
        */
        display: flex;
        justify-content: center;
        align-items: center;
      }
      .occupied-dialog-container {
        width: 450px;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33); 
        transition: all .3s ease;
      }
      
    </style>
    <script type="text/javascript">
      if($json_lock){
        jQuery(document).ready(function(){
          Occupied.init({el: '$app_el', lock: $json_lock, screen: '$screen_id', back: '$referer'});
        });
      }else{
        console.log('locking not working', $json_lock);
        alert('lock broken!');
      }
    </script>
HTML;
    echo $output;
  }


  // Loads javascript into the page and generates popups on lock.
  public static function protect(){
    $screen = get_current_screen();
    return self::padlock_generate($screen->id);
  }

  // Checks whether a given page is owned by the current user
  public static function is_authorized($screen=null){
    if(!$screen){
      $screen = get_current_screen()->id;
    }
    $current_user = wp_get_current_user(); 
    $lock = self::padlock_get($screen);

    if($lock && isset($lock["owner_id"]) && ($lock["owner_id"] === $current_user->ID)){
      return true;
    }else{
      return false;
    }
  }

  // Create or update the padlock for the screen
  public static function padlock_generate($screen_id, $take_over=false){

    if(!$screen_id){ 
      return false;
    }

    $current_user = wp_get_current_user(); 
    $lock = self::padlock_get($screen_id);
    $now = strtotime("now");
    $valid = false;
    
    if(!$take_over && $lock && isset($lock["updated_at"]) && isset($lock["owner_id"])  && ($now < strtotime(self::SESSION_LENGTH, $lock["updated_at"]))){
      // Update current lock if it belongs to the current user
      if($current_user->ID === $lock["owner_id"]){
        $lock["updated_at"] = $now;
        $valid = true;
      }
    }else{
      // Generate new lock
      $lock = self::generate($current_user, $now);
      $valid = true;
    }
    //Save
    self::padlock_save($screen_id, $lock);
    //Inflate
    $lock['authenticated'] = $valid;
    //return
    return $lock; 
  }

  private static function generate($user, $updated){
    $avatar_url = get_avatar_url($user->ID);
    return array("owner_id" => $user->ID, "owner_avatar_url" => $avatar_url,  "owner_display_name" => $user->display_name, "updated_at" => $updated);
  }

  private static function keyring() {
    return get_option(self::WP_OPTIONS_NAME);  
  }

  public static function padlock_get($screen_id){
    $keyring = self::keyring();
    if (!isset($keyring[$screen_id])){
      return null;
    }else{
      return $keyring[$screen_id];
    }
  }

  public static function padlock_save($screen_id, $payload){
    $keyring = self::keyring();
    $keyring[$screen_id] = $payload;
    return update_option(self::WP_OPTIONS_NAME, $keyring);
  }

}

    
?>
