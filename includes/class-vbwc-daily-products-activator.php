<?php

/**
 * Fired during plugin activation
 *
 * @link       http://verbbrands.com/
 * @since      1.0.0
 *
 * @package    Vbwc_Daily_Products
 * @subpackage Vbwc_Daily_Products/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Vbwc_Daily_Products
 * @subpackage Vbwc_Daily_Products/includes
 * @author     Zach Townsend <zach@verbbrands.com>
 */
class Vbwc_Daily_Products_Activator {

	public static $category_slug = 'wcdp-product-day';

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_action( 'init', [$this, 'insert_days'], 999 );
	}

	public static function insert_days() {
		$day_array = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

		foreach ($day_array as $day) {
			if ( ! term_exists( $day, 'wcdp-product-day' ) ) {
				wp_insert_term( $day, 'wcdp-product-day', ['slug' => sanitize_title( 'WCDP2 ' . $day )] );
			}
		}
	}

}
