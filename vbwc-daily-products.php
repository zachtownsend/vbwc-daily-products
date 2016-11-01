<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://verbbrands.com/
 * @since             1.0.0
 * @package           Vbwc_Daily_Products
 *
 * @wordpress-plugin
 * Plugin Name:       Verb Brands WC Daily Products
 * Plugin URI:        http://verbbrands.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Zach Townsend
 * Author URI:        http://verbbrands.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vbwc-daily-products
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vbwc-daily-products-activator.php
 */
function activate_vbwc_daily_products() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vbwc-daily-products-activator.php';
	Vbwc_Daily_Products_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vbwc-daily-products-deactivator.php
 */
function deactivate_vbwc_daily_products() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vbwc-daily-products-deactivator.php';
	Vbwc_Daily_Products_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_vbwc_daily_products' );
register_deactivation_hook( __FILE__, 'deactivate_vbwc_daily_products' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-vbwc-daily-products.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vbwc_daily_products() {

	$plugin = new Vbwc_Daily_Products();
	$plugin->run();

}
run_vbwc_daily_products();
