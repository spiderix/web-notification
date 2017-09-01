<?php

/**
 * Web Notification
 *
 * @package     Web Notification
 * @author      Sebastian Muszyński
 * @copyright   2017 Web Notification
 * @license     MIT
 *
 * Plugin Name: Web Notification
 * Description: Plugin can send web push notification to you subs
 * Version:     0.0.1
 * Author:      Sebastian Muszyński
 * Text Domain: web-Notification
 * License:     MIT
 *
 */

// Block direct access to file
defined( 'ABSPATH' ) or die( 'Not Authorized!' );

// Plugin Defines
define( "WEB_PAGINATION_FILE", __FILE__ );
define( "WEB_PAGINATION_DIR", dirname(__FILE__) );
define( "WEB_PAGINATION_INCLUDE_DIR", dirname(__FILE__) . 'include' );
define( "WEB_PAGINATION_DIR_BASENAME", plugin_basename( __FILE__ ) );
define( "WEB_PAGINATION_DIR_PATH", plugin_dir_path( __FILE__ ) );
define( "WEB_PAGINATION_DIR_URL", plugins_url( null, __FILE__ ) );

// Require the main class file
require_once( dirname(__FILE__) . '/include/class-main.php' );