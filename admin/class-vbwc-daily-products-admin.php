<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://verbbrands.com/
 * @since      1.0.0
 *
 * @package    Vbwc_Daily_Products
 * @subpackage Vbwc_Daily_Products/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Vbwc_Daily_Products
 * @subpackage Vbwc_Daily_Products/admin
 * @author     Zach Townsend <zach@verbbrands.com>
 */
class Vbwc_Daily_Products_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Slug for Product Day taxonomy
	 */
	public $category_slug = 'wcdp-product-day';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// set category slug to avoid conflicts with existing taxonomies
		// $this->set_category_slug();

	}

	public function insert_days() {
		$day_array = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

		foreach ($day_array as $day) {
			if ( ! term_exists( $day, $this->category_slug ) ) {
				wp_insert_term( $day, $this->category_slug, ['slug' => sanitize_title( 'WCDP ' . $day )] );
			}
		}
	}

	public function register_day_taxonomy() {
		
		/**
		 * Product Day Taxonomy
		 */
		$labels = array(
			'name'					=> _x( 'Product Days', 'Taxonomy plural name', $this->plugin_name ),
			'singular_name'			=> _x( 'Product Day', 'Taxonomy singular name', $this->plugin_name ),
			'search_items'			=> __( 'Search Product Days', $this->plugin_name ),
			'popular_items'			=> __( 'Popular Product Days', $this->plugin_name ),
			'all_items'				=> __( 'All Product Days', $this->plugin_name ),
			'parent_item'			=> __( 'Parent Product Day', $this->plugin_name ),
			'parent_item_colon'		=> __( 'Parent Product Day', $this->plugin_name ),
			'edit_item'				=> __( 'Edit Product Day', $this->plugin_name ),
			'update_item'			=> __( 'Update Product Day', $this->plugin_name ),
			'add_new_item'			=> __( 'Add New Product Day', $this->plugin_name ),
			'new_item_name'			=> __( 'New Product Day Name', $this->plugin_name ),
			'add_or_remove_items'	=> __( 'Add or remove Product Days', $this->plugin_name ),
			'choose_from_most_used'	=> __( 'Choose from most used', $this->plugin_name, $this->plugin_name ),
			'menu_name'				=> __( 'Product Day', $this->plugin_name ),
		);
		
		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_admin_column' => false,
			'hierarchical'      => true,
			'show_tagcloud'     => false,
			'show_ui'           => true,
			'query_var'         => true,
			'rewrite'           => true,
			'query_var'         => true,
			'capabilities'      => array(),
		);
		
		register_taxonomy( $this->category_slug, array( 'product' ), $args );

		$this->insert_days();
	}

	/**
	 * Add new settings tab in Woocommerce > Settings
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['wcdp_settings_tab'] = __('Daily Product Management', $this->plugin_name);
		return $settings_tabs;
	}

	public function add_section( $sections ) {
		$sections = __('Test Section', $this->plugin_name);
	}

	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	public function get_product_cat_values() {
		$product_cats = get_terms('product_cat');
		$array = [];
		foreach ($product_cats as $cat) {
			$array[$cat->slug] = $cat->name;
		}

		return $array;
	}

	public function test( $value ) {
		var_dump( $value );
	}

	public function get_settings() {
		// https://www.skyverge.com/blog/add-custom-options-to-woocommerce-settings/
		$settings = array(
			'days_title' => array(
	            'name'     => __( 'Active Days', $this->plugin_name ),
	            'type'     => 'title',
	            'desc'     => '',
	            'id'       => 'wcdp_days_section_title'
	        ),
	        'day_monday' => array(
	            'name' => __( 'Monday', $this->plugin_name ),
	            'type' => 'checkbox',
	            // 'checkboxgroup' => 'start',
	            'default' => 'no',
	            'id'   => 'wcdp_days_section_monday'
	        ),
	        'day_tuesday' => array(
	            'name' => __( 'Tuesday', $this->plugin_name ),
	            'type' => 'checkbox',
	            'default' => 'no',
	            'id'   => 'wcdp_days_section_tuesday'
	        ),
	        'day_wednesday' => array(
	            'name' => __( 'Wednesday', $this->plugin_name ),
	            'type' => 'checkbox',
	            'default' => 'no',
	            'id'   => 'wcdp_days_section_wednesday'
	        ),
	        'day_thursday' => array(
	            'name' => __( 'Thursday', $this->plugin_name ),
	            'type' => 'checkbox',
	            'default' => 'no',
	            'id'   => 'wcdp_days_section_thursday'
	        ),
	        'day_friday' => array(
	            'name' => __( 'Friday', $this->plugin_name ),
	            'type' => 'checkbox',
	            'default' => 'no',
	            'id'   => 'wcdp_days_section_friday'
	        ),
	        'day_saturday' => array(
	            'name' => __( 'Saturday', $this->plugin_name ),
	            'type' => 'checkbox',
	            'default' => 'no',
	            'id'   => 'wcdp_days_section_saturday'
	        ),
	        'day_sunday' => array(
	            'name' => __( 'Sunday', $this->plugin_name ),
	            'type' => 'checkbox',
	            // 'checkboxgroup' => 'end',
	            'default' => 'no',
	            'id'   => 'wcdp_days_section_sunday'
	        ),
	        'days_end' => array(
	             'type' => 'sectionend',
	             'id' => 'wc_settings_tab_demo_section_end'
	        ),
	        'active_cats_title' => array(
	        	'type' => 'title',
	        	'name' => __('Active Categories', $this->plugin_name),
	        	'id' => 'wcdp_active_cats_title'
	        ),
	        'active_cats' => array(
	        	'name' => __('Active Categories'),
	        	'type' => 'multiselect',
	        	'options' => $this->get_product_cat_values(),
	        	'id' => 'wcdp_active_cats'
	        ),
	        'active_cats_end' => array(
	        	'type' => 'sectionend',
	        	'id' => 'wcdp_active_cats_sectionend'
	        ),
	        'test' => array(
	        	'type' => 'day',
	        	'id' => 'test'
	        )
		);
		return apply_filters( 'wc_settings_tab_wcdp_settings_tab', $settings );
	}

	public function update_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vbwc_Daily_Products_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vbwc_Daily_Products_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/vbwc-daily-products-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vbwc_Daily_Products_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vbwc_Daily_Products_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/vbwc-daily-products-admin.js', array( 'jquery' ), $this->version, false );

	}

}
