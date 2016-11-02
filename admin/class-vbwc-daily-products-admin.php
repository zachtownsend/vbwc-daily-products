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
	 * Array storing the days of the week
	 */
	public $day_array = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

	/**
	 * Store the active days
	 */
	public $active_days = array();

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
		$this->get_day_settings();

	}

	public function init_acf_settings() {
		$this->get_day_settings();
		// var_dump($this->active_days);
		$this->add_acf_options_page();
		$this->add_acf_fields();
	}

	public function get_day_settings() {
		foreach ($this->day_array as $day) {
			if ( get_option( 'wcdp_day_' . sanitize_title( $day ) ) === 'yes' ) {
				$this->active_days[] = $day;
			}
		}
	}

	public function get_limited_categories() {
		$category_array = array();

		return $category_array;
	}

	public function add_acf_options_page() {
		// Product Settings
		acf_add_options_sub_page( 
			array(
				'page_title' => 'Product Settings',
				'parent_slug' => 'edit.php?post_type=product',
				'post_id' => 'product_settings',
				'capability' => 'manage_options'
			) 
		);
	}

	public function get_acf_fields() {

		$field_array = array();
		
		foreach ($this->active_days as $day) {
			$sanitized_day = sanitize_title( $day );

			$field = array(
				'key' => 'field_wcdp-' . $sanitized_day,
				'label' => $day,
				'name' => 'product_day_' . $sanitized_day,
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'post_type' => array (
					0 => 'product',
				),
				'taxonomy' => array (
					0 => 'product_cat:main-meal',
				),
				'filters' => array (
					0 => 'search'
				),
				'elements' => array (
					0 => 'featured_image',
				),
				'min' => 3,
				'max' => 3,
				'return_format' => 'object',
			);

			array_push($field_array, $field);
		}

		return $field_array;
	}

	public function add_acf_fields() {
		if( function_exists('acf_add_local_field_group') ):

		acf_add_local_field_group(array (
			'key' => 'group_58175a8ccb49e',
			'title' => 'Product Day Selector',
			'fields' => $this->get_acf_fields(),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'acf-options-product-settings',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		endif;
	}

	public function get_dependencies() {
		require plugin_dir_path( dirname(__FILE__) ) . 'includes/class-vbwc-daily-products-selector.php';
	}

	public function insert_days() {
		foreach ($this->day_array as $day) {
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

	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	public function get_product_cat_values() {
		$product_cats = get_terms('product_cat');
		$array = ['null' => '-- Please select --'];
		foreach ($product_cats as $cat) {
			$array[$cat->slug] = $cat->name;
		}

		return $array;
	}

	public function get_settings() {
		// https://www.skyverge.com/blog/add-custom-options-to-woocommerce-settings/
		$settings = array(
			array(
				'title' => __( 'Day Settings', $this->plugin_name ),
				'type' 	=> 'title',
				'desc' 	=> '',
				'id' 	=> 'wcdp_day_settings',
			),
			array(
				'title'         => __( 'Active Days', $this->plugin_name ),
				'desc' => __( 'Monday', $this->plugin_name ),
				'id' => 'wcdp_day_monday',
				'default' => 'no',
				'type' => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			array(
				'desc' => __( 'Tuesday', $this->plugin_name ),
				'id' => 'wcdp_day_tuesday',
				'default' => 'no',
				'type' => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc' => __( 'Wednesday', $this->plugin_name ),
				'id' => 'wcdp_day_wednesday',
				'default' => 'no',
				'type' => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc' => __( 'Thursday', $this->plugin_name ),
				'id' => 'wcdp_day_thursday',
				'default' => 'no',
				'type' => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc' => __( 'Friday', $this->plugin_name ),
				'id' => 'wcdp_day_friday',
				'default' => 'no',
				'type' => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc' => __( 'Saturday', $this->plugin_name ),
				'id' => 'wcdp_day_saturday',
				'default' => 'no',
				'type' => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc' => __( 'Sunday', $this->plugin_name ),
				'id' => 'wcdp_day_sunday',
				'default' => 'no',
				'type' => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => 'end'
			),
			array(
				'title' => __( 'Limit to Categories', $this->plugin_name ),
				'desc' => __( 'Would you like to limit to specific categories?', $this->plugin_name ),
				'type' => 'checkbox',
				'default' => 'no',
				'id' => 'wcdp_limit_to_cat'
			),
			array(
	        	'name' => __('Active Categories', $this->plugin_name),
	        	'type' => 'multiselect',
	        	'default' => 'none',
	        	'options' => $this->get_product_cat_values(),
	        	'id' => 'wcdp_active_cats',
	        	'show_if_checked' => 'yes'
	        ),
			array(
				'type' 	=> 'sectionend',
				'id' 	=> 'wcdp_days_sectionend'
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
