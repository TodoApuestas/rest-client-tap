<?php

/**
 * @link              http://www.linkedin.com/in/mrbrazzi/
 * @since             1.1
 * @package           RestClientTap
 *
 * @wordpress-plugin
 * Plugin Name:       Rest Client TAP
 * Plugin URI:        https://www.wordpress.org/plugins/rest-client-tap
 * Description:       Rest client plugin to TodoApuestas API services
 * Version:           1.1
 * Author:            Alain Sanchez <luka.ghost@gmail.com>
 * Author URI:        http://www.linkedin.com/in/mrbrazzi/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rest-client-tap
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rest-client-tap-activator.php
 */
function rest_client_tap_activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rest-client-tap-activator.php';
	RestClientTapActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rest-client-tap-deactivator.php
 */
function rest_client_tap_deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rest-client-tap-deactivator.php';
	RestClientTapDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'rest_client_tap_activate_plugin_name' );
register_deactivation_hook( __FILE__, 'rest_client_tap_deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rest-client-tap.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function rest_client_tap_run_plugin_name() {

	$plugin = new RestClientTap();
	$plugin->run();

}
rest_client_tap_run_plugin_name();
