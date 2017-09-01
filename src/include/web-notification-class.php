<?php
defined( 'ABSPATH' ) or die( 'Not Authorized!' );

include_once 'push-notification/WebPush.php';
use Minishlink\WebPush\WebPush;

class Web_Notification {

  private $settings;

  public function __construct() {

    // Plugin uninstall hook
    register_uninstall_hook( WEB_PAGINATION_FILE, array(__CLASS__, 'plugin_uninstall') );

    // Plugin activation/deactivation hooks
    register_activation_hook( WEB_PAGINATION_FILE, array($this, 'plugin_activate') );
    register_deactivation_hook( WEB_PAGINATION_FILE, array($this, 'plugin_deactivate') );

    // Plugin Actions
    add_action( 'plugins_loaded', array($this, 'plugin_init') );
    add_action( 'wp_enqueue_scripts', array($this, 'plugin_enqueue_scripts') );

    //add action after post publish
    add_action( 'publish_post', 'plugin_send');
    
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

  /**
  * Plugin init function
  * init the polugin textDomain
  * @method plugin_init
  */


  function register_plugin_js($plugin_array){
    $plugin_array['webpagination'] = WEB_PAGINATION_DIR_URL .'/assets/js/admin/admin.js';
    return $plugin_array;
  }

  function register_plugin_buttons($buttons){
    array_push( $buttons, 'webinsert');
    return $buttons;
  }
  

  function register_plugin_content($content){
    if(is_singular() && strpos($content,'<hr class="pag-break" />')){
      $content = explode('<hr class="pag-break" />', $content);
      $nav = '<nav class="pagination">
                ';
      $content_mod = '';
      foreach ($content as $key => $page) {
        $content_mod .= '<div class="pagination_content '.($key>0?'hidden':'').' page-'.($key+1).'">'.$page.'</div>';
        $nav.='<a href="#" data-page="'.($key+1).'" class="pag-num '.($key>0?'':'active-page').'">'.($key+1).'</a>';
      }

      $nav .='<a class="pag-btn pag-all" >View All</a>
              <a href="#" class="pag-btn pag-prev disabled"> < </a>
              <a href="#" class="pag-btn pag-next" > > NEXT PAGE</a>
        </nav>';
      $content = $content_mod.$nav;
    }else{
      $content = str_replace('<hr class="pag-break" />','',$content);
    }
    return $content;
  }
  

  function plugin_init() {
    load_plugin_textDomain( 'web-pagination', false, dirname(WEB_PAGINATION_DIR_BASENAME) . '/languages' );
  }

  /**
   * Add the plugin menu page(s)
   * @method plugin_add_settings_pages
   */
  function plugin_add_settings_pages() {

    add_menu_page(
      __('Web Pagination', 'web-pagination'),
      __('Web Pagination', 'web-pagination'),
      'administrator', // Menu page capabilities
      'web-pagination-settings', // Page ID
      array($this, 'plugin_settings_page'), // Callback
      'none', // No icon
      null
    );

  }

  /**
  * Register the main Plugin Settings
  * @method plugin_register_settings
  */
  function plugin_register_settings() {

    register_setting( 'web-pagination-settings-group', 'web-pagination_main_options', array($this, 'plugin_sanitize_settings') );

    add_settings_section( 'main', __('Main Settings', 'web-pagination'), array( $this, 'main_section_callback' ), 'web-pagination-settings' );

    add_settings_field( 'first_option', 'First Option', array( $this, 'first_option_callback' ), 'web-pagination-settings', 'main' );

  }

  /**
   * The text to display as description for the main section
   * @method main_section_callback
   */
  function main_section_callback() {
    return _e( 'Start adding from here you plugin settings.', 'web-pagination' );
  }

  /**
   * Create the option html input
   * @return html
   */
  function first_option_callback() {
    return printf(
      '<input type="text" id="first_option" name="web-pagination_main_options[first_option]" value="%s" />',
      isset( $this->settings['first_option'] ) ? esc_attr( $this->settings['first_option']) : ''
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
  function plugin_enqueue_admin_scripts() {
    wp_register_style( 'web-pagination_admin_style', WEB_PAGINATION_DIR_URL . '/css/admin.css', array(), null );
    wp_register_script( 'web-pagination_admin_script', WEB_PAGINATION_DIR_URL . '/js/admin.js', array(), null, true );
    wp_enqueue_script('jquery');
    wp_enqueue_style('web-pagination_admin_style');
    wp_enqueue_script('web-pagination_admin_script');
  }

  /**
  * Enqueue the main Plugin user scripts and styles
  * @method plugin_enqueue_scripts
  */
  function plugin_enqueue_scripts() {
    wp_register_style( 'web-pagination_user_style', WEB_PAGINATION_DIR_URL . '/assets/css/user/user.css', array(), null );
    wp_register_script( 'web-pagination_user_script', WEB_PAGINATION_DIR_URL . '/assets/js/user/user.js', array(), null, true );
    wp_enqueue_script('jquery');
    wp_enqueue_style('web-pagination_user_style');
    wp_enqueue_script('web-pagination_user_script');
  }

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

      <div class="card">

        <?php $this->settings = get_option( 'web-notification_main_options' ); ?>

        <form method="post" action="options.php">

          <?php settings_fields( 'web-pagination-settings-group' ); ?>
          <?php do_settings_sections( 'web-pagination-settings' ); ?>

          <?php submit_button(); ?>

        </form>

      </div>

    </div><?php

    return print( ob_get_clean() );

  }

  function plugin_send(array $sub, Object $msg){
    // {"body": "tekst", "link":"http://www.ujk.edu.pl"}

        $webPush = new WebPush();

        foreach ($sub as $notification) {
            $notification = json_decode($notification);
            $webPush->sendNotification(
                $notification['endpoint'],
                $msg, // optional (defaults null)
                $notification->keys['p256dh'], // optional (defaults null)
                $notification->keys['auth'] // optional (defaults null)
            );
        }
        $webPush->flush();
  }

}


new Web_Notification;
