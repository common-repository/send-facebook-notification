<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              apurba.me
 * @since             1.0.0
 * @package           Fb_Notify
 *
 * @wordpress-plugin
 * Plugin Name:       Send FaceBook Notification
 * Plugin URI:        themexplorer.org
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Apurba
 * Author URI:        www.apurba.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fb-notify
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fb-notify-activator.php
 */
function activate_fb_notify() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fb-notify-activator.php';
	Fb_Notify_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fb-notify-deactivator.php
 */
function deactivate_fb_notify() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fb-notify-deactivator.php';
	Fb_Notify_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fb_notify' );
register_deactivation_hook( __FILE__, 'deactivate_fb_notify' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fb-notify.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fb_notify() {

	$plugin = new Fb_Notify();
	$plugin->run();

}
run_fb_notify();
