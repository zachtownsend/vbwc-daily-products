<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://verbbrands.com/
 * @since      1.0.0
 *
 * @package    Vbwc_Daily_Products
 * @subpackage Vbwc_Daily_Products/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Vbwc_Daily_Products
 * @subpackage Vbwc_Daily_Products/includes
 * @author     Zach Townsend <zach@verbbrands.com>
 */
class Vbwc_Daily_Products_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		add_action('init', [$this, 'delete_days']);
	}

	public static function delete_days() {
		foreach ($variable as $key => $value) {
			$day_array = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

			foreach ($day_array as $day) {
				if ( term_exists( $day, 'wcdp-product-day' ) ) {
					wp_delete_term( $day, 'wcdp-product-day' );
				}
			}
		}
	}

}
