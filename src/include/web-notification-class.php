<?php
defined( 'ABSPATH' ) or die( 'Not Authorized!' );

include_once 'push-notification/WebPush.php';
use Minishlink\WebPush\WebPush;


class Web_Notification {

  private $settings;
  private $table_name;

  public function __construct() {
    global $wpdb;
    $this->table_name = $wpdb->prefix . "notification_sub";

    // Plugin uninstall hook
    register_uninstall_hook( WEB_NOTIFICATION_FILE, array(__CLASS__, 'plugin_uninstall') );

    // Plugin activation/deactivation hooks
    register_activation_hook( WEB_NOTIFICATION_FILE, array($this, 'plugin_activate') );
    register_deactivation_hook( WEB_NOTIFICATION_FILE, array($this, 'plugin_deactivate') );

    // Plugin Actions
    // add_action( 'plugins_loaded', array($this, 'plugin_init') );
    // add_action( 'wp_enqueue_scripts', array($this, 'plugin_enqueue_scripts') );

    //add admin menu page
    add_action( 'admin_menu', array( $this, 'plugin_add_settings_pages' ) );
    add_action( 'admin_init', array( $this, 'plugin_register_settings' ) );

    //add box to post-new.php

    add_action( 'add_meta_boxes', array( $this,'wpdocs_register_meta_boxes' ));
    

    //add action after post publish
    add_action( 'publish_post', array( $this,'plugin_send'));
    
  }

  function wpdocs_register_meta_boxes() {
    add_meta_box(
        'notification_box_post',
        'Notification',
        array( $this,'notification_box_html'),
        'post',
        'side',
        'high',
        null                 
    );
  }

  /**
   * Plugin uninstall function
   * called when the plugin is uninstalled
   * @method plugin_uninstall
   */
  public static function plugin_install() {
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            sub tinytext NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

   }
  /**
   * Plugin uninstall function
   * called when the plugin is uninstalled
   * @method plugin_uninstall
   */
  public static function plugin_uninstall() { }

  /**
  * Plugin activation function
  * called when the plugin is activated
  * @method plugin_activate
  */
  public function plugin_activate() { }

  /**
  * Plugin deactivate function
  * is called during plugin deactivation
  * @method plugin_deactivate
  */
  public function plugin_deactivate() { }
 

  function plugin_init() {
    // load_plugin_textDomain( 'web-notification', false, dirname(WEB_NOTIFICATION_DIR_BASENAME) . '/languages' );
  }

  /**
   * Add the plugin menu page(s)
   * @method plugin_add_settings_pages
   */
  function plugin_add_settings_pages() {
      //create new top-level menu
    $page = add_options_page(
            'Settings Admin', 
            'Notifications', 
            'manage_options', 
            'web-notification', 
            array( $this, 'plugin_settings_page' )
        );

    add_action( "admin_print_styles-{$page}", array( $this, 'plugin_enqueue_admin_style' ) );
  }

  function notification_box_html($post){
   return printf(
      '<input type="checkbox" id="web-notification_main_options[autoSend]" name="web-notification_main_options[autoSend]" %s> Send notification after Publish',
      get_option("autoSend") !== null ? 'checked = "'.esc_attr( get_option( "autoSend" )).'" value="true"' : ''
    );
  }

  /**
  * Register the main Plugin Settings
  * @method plugin_register_settings
  */
  function plugin_register_settings() {

    register_setting( 'web-notification-settings-group', 'web-notification_main_options', array($this, 'plugin_sanitize_settings') );

    add_settings_section( 'main', __('Auth API Settings', 'web-notification'), array( $this, 'main_section_callback' ), 'web-notification-settings' );

    add_settings_field( 'serverKey', 'Public key', array( $this, 'server_key_callback' ), 'web-notification-settings', 'main' );
    add_settings_field( 'privateKey', 'Private key', array( $this, 'private_key_callback' ), 'web-notification-settings', 'main' );
    add_settings_field( 'autoSend', 'Automaticly send notification after Publish', array( $this, 'auto_send_callback' ), 'web-notification-settings', 'main' );

  }

  /**
   * The text to display as description for the main section
   * @method main_section_callback
   */
  function main_section_callback() {
    // return _e( 'Start adding from here you plugin settings.', 'web-notification' );
  }

  /**
   * Create the option html input
   * @return html
   */
  function server_key_callback() {
    return printf(
      '<textarea rows="4" cols="50" id="serverKey" name="web-notification_main_options[serverKey]" >%s</textarea>',
      isset( $this->settings['serverKey'] ) ? esc_attr( $this->settings['serverKey']) : ''
    );
  }

  /**
   * Create the option html input
   * @return html
   */
  function private_key_callback() {
    return printf(
      '<input type="text" id="privateKey" style="width:328px" name="web-notification_main_options[privateKey]" value="%s" />',
      isset( $this->settings['privateKey'] ) ? esc_attr( $this->settings['privateKey']) : ''
    );
  }
  /**
   * Create the option html input
   * @return html
   */
  function auto_send_callback() {
    return printf(
      '<input type="checkbox" id="autoSend" name="web-notification_main_options[autoSend]" %s>',
      isset( $this->settings['autoSend'] ) ? "checked = ".esc_attr( $this->settings['autoSend']) : ''
    );
  }

  /** 
   * Sanitize the settings values before saving it
   * @param  mixed $input The settings value
   * @return mixed        The sanitized value
   */
  function plugin_sanitize_settings($input) {
    return $input;
  }

  /**
  * Enqueue the main Plugin admin scripts and styles
  * @method plugin_enqueue_scripts
  */
  function plugin_enqueue_admin_style() {
    wp_register_style( 'web-notification_admin_style', WEB_NOTIFICATION_DIR_URL . '/css/admin.css', array(), null );
    wp_enqueue_style('web-notification_admin_style');
  }
  /**
  * Enqueue the main Plugin admin scripts and styles
  * @method plugin_enqueue_scripts
  */
  // function plugin_enqueue_admin_style() {
  //   wp_register_style( 'web-notification_admin_style', WEB_NOTIFICATION_DIR_URL . '/css/admin.css', array(), null );
  //   wp_enqueue_style('web-notification_admin_style');
  // }


  /**
  * Plugin main settings page
  * @method plugin_settings_page
  */
  function plugin_settings_page() {

    ob_start(); ?>

    <div class="wrap">

      <div class="card">

        <h1><?php _e( 'Web notification', 'web-notification' ); ?></h1>

        <p><?php _e( 'Start from here to build you awesome plugin, using this basic setup.', 'web-notification' ); ?></p>

      </div>
      <div class="plugin-row">
        <div class="card">
          <?php $this->settings = get_option( 'web-notification_main_options' ); ?>
          <form method="post" action="options.php">
            <?php settings_fields( 'web-notification-settings-group' ); ?>
            <?php do_settings_sections( 'web-notification-settings' ); ?>

            <?php submit_button(); ?>
          </form>
        </div>
        <div>
          <div class="card" style="margin-left:20px">
            <h2><?php _e( 'Test your notification', 'web-notification-test' ); ?></h2>
            <!-- <form method="post" action="/"> -->
              <textarea cols="50" rows="5" id="notification_test" name="web-notification_main_options[notification_test]"></textarea>
              <?php submit_button('Send test notification'); ?>
            <!-- </form> -->
          </div>
        </div>
      </div>

    </div><?php

    return print( ob_get_clean() );

  }

  function plugin_send($post_id){
    global $wpdb;
    // {"body": "tekst", "link":"http://www.ujk.edu.pl"}

    if($_POST['web-notification_main_options']['autoSend']){
      //send notification

      $title = get_the_title( $post_id );
      $link = get_permalink($post_id);
      
      $subs = $wpdb->get_results("SELECT sub FROM ".$this->table_name);

      
      foreach ($subs as $notification) {
        $notification = json_decode($notification->sub);
        var_dump($notification);

        $server_key = 'BNMM35rJlOQzgZg_bx8ggoS5l2ddc1SirREEUg5D5Qi8YNSjstZhyAfNcRLdM8Rd4G5hsZRi8Uy-M93nK0fPu7g=';
			
        $fields = array();
        $fields['data'] = (object)["body"=>$title, "link"=>$link];
        // if(is_array($notification)){
        //   $fields['registration_ids'] = $notification;
        // }else{
        //   $fields['to'] = $notification;
        // }
        //header with content_type api key
        $headers = array(
        'Content-Type:application/json',
              'Authorization:key='.$server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)			
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $notification->endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
          die('Oops! FCM Send Error: ' . curl_error($ch));
        }
        var_dump($result);
        curl_close($ch);
      }
    }
    exit();
  }

}
// {"endpoint":"https://fcm.googleapis.com/fcm/send/eo1wjTLNl64:APA91bGg5mp7oPfcxy6kTwiXt9A5-PC9hwLTodzcqNdTgvEjqi2dvvC-blyJQ0TpzeQi3-qCGrp3vPfZ2ZAdcze0eVpyZv3JQRC5gwADawQCLdICtTaiGZREPjJNnwVYxYvJAiZfikSW","expirationTime":null,"keys":{"p256dh":"BDWnX5H30jorPlPKCLg5M-bCQRGJ6wNGmG0MVmmFT4PnJG-G_m2_zxsscLCxq9EXx7pP652D5VT-vFbHE9XMLnc=","auth":"qiuJCfP7mpaDfCjgbEsHRQ=="}}

new Web_Notification;
