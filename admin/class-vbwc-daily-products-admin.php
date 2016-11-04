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

		$this->get_day_settings();

		// On save of product day settings
		add_action('acf/save_post', [$this, 'update_products'], 999);
		
		// Reset product day category at given time
		add_action('weekly_day_reset', [$this, 'weekly_day_reset']);

		// Reset product stock
		add_action('daily_stock_reset', [$this, 'daily_stock_reset']);

		// On Settings update
		add_action( 'woocommerce_update_options_wcdp_settings_tab', [$this, 'update_settings'] );

		add_action( 'woocommerce_settings_saved', [$this, 'settings_saved']);

	}

	public function error_log( $message ) {
		ob_start();
		var_export( $message );
		$message = ob_get_clean();
		error_log( $message );
	}

	/**
	 * Advanced Custom Fields methods
	 */

	// Initialise ACF Settings
	public function init_acf_settings() {
		$this->get_day_settings();
		$this->add_acf_options_page();

		if ( post_type_exists( 'pickup-point' ) ) {
			$pups = get_posts( 'post_type=pickup-point' );

			foreach ($pups as $pup) {
				$this->add_acf_group($pup);
			}
			
		} else {
			$this->add_acf_group();
		}		
	}

	// Add options page
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

	// Gets the taxonomies to limit the ACF product search to
	public function get_acf_field_taxonomies() {
		$cat_array = '';

		if ( get_option( 'wcdp_limit_to_cat' ) === 'yes' ) {
			$chosen_cats = get_option( 'wcdp_active_cats' );

			$cat_array = array();

			foreach ($chosen_cats as $cat) {
				if ( 'null' !== $cat) {
					$cat_array[] = 'product_cat:' . $cat;
				}
				
			}
		}

		return $cat_array;
	}

	// Gets the relationship fields to use
	public function get_acf_fields( $pup ) {

		if ( $pup ) {
			$suffix = '_' . $pup->ID;
		} else {
			$suffix = '';
		}

		$field_array = array();
		
		foreach ($this->active_days as $day) {
			$suffix = sanitize_title( $day ) . $suffix;

			$field = array(
				'key' => 'field_wcdp-' . $suffix,
				'label' => $day,
				'name' => 'product_day_' . $suffix,
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
				'taxonomy' => $this->get_acf_field_taxonomies(),
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

	// Adds the ACF group
	public function add_acf_group( $pup ) {

		if ( $pup ) {
			$suffix = '_' . $pup->ID;
			$title = 'Pick Up Point: ' . $pup->post_title;
		} else {
			$suffix = '';
			$title = 'Product Day Selector';
		}

		if( function_exists('acf_add_local_field_group') ):

		acf_add_local_field_group(array (
			'key' => 'group_wcdp' . $suffix,
			'title' => $title,
			'fields' => $this->get_acf_fields($pup),
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

	/**
	 * Utility methods
	 */

	public function get_day_settings() {
		foreach ($this->day_array as $day) {
			if ( get_option( 'wcdp_day_' . sanitize_title( $day ) ) === 'yes' ) {
				$this->active_days[] = $day;
			}
		}
	}

	// Insert days of the week into Product Day taxonomy
	public function insert_days() {
		foreach ($this->day_array as $day) {
			if ( ! term_exists( $day, $this->category_slug ) ) {
				wp_insert_term( $day, $this->category_slug, ['slug' => sanitize_title( 'WCDP ' . $day )] );
			}
		}
	}

	// Get product categories for select field types
	public function get_product_cat_values() {
		$product_cats = get_terms('product_cat', array( 'hide_empty' => false ) );
		$array = ['null' => '-- Please select --'];
		foreach ($product_cats as $cat) {
			$array[$cat->slug] = $cat->name;
		}

		return $array;
	}

	// Gets the time options for select field types
	public function get_time_options() {
		$option_array = array();
		$hour = 0;
		while ($hour < 24) {
			$hour = $hour > 9 ? $hour : '0' . $hour;
			$option_array[$hour . ':00'] = $hour . ':00';
			$option_array[$hour . ':30'] = $hour . ':30';
			$hour++;
		}
		return $option_array;
	}

	// Gets the day options for select field types
	public function get_day_options() {
		$option_array = array();
		foreach ($this->day_array as $day) {
			$option_array[$day] = $day;
		}
		return $option_array;
	}

	// Get array of day category ID's
	public function get_day_terms_id_array() {
		$terms = get_terms( $this->category_slug );
		$id_array = array();

		foreach ($terms as $term) {
			$id_array[] = $term->term_id;
		}

		return $id_array;
	}
	

	/**
	 * General Initialisation
	 */
	
	// Register the Day taxonomy
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
	 * Woocommerce Admin
	 */
	
	// Add Settings tab to Woocommerce > Settings
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['wcdp_settings_tab'] = __('Daily Product Management', $this->plugin_name);
		return $settings_tabs;
	}

	// Get WC settings to go in settings tab
	public function get_settings() {
		// https://www.skyverge.com/blog/add-custom-options-to-woocommerce-settings/
		$settings = array(
			
			/**
			 * Day Settings
			 */
			array(
				'title' => __( 'Day Settings', $this->plugin_name ),
				'type' 	=> 'title',
				'desc' 	=> '',
				'id' 	=> 'wcdp_day_settings',
			),
			array(
				'title'         => __( 'Active Days', $this->plugin_name ),
				'desc'          => __( 'Monday', $this->plugin_name ),
				'id'            => 'wcdp_day_monday',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			array(
				'desc'          => __( 'Tuesday', $this->plugin_name ),
				'id'            => 'wcdp_day_tuesday',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc'          => __( 'Wednesday', $this->plugin_name ),
				'id'            => 'wcdp_day_wednesday',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc'          => __( 'Thursday', $this->plugin_name ),
				'id'            => 'wcdp_day_thursday',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc'          => __( 'Friday', $this->plugin_name ),
				'id'            => 'wcdp_day_friday',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc'          => __( 'Saturday', $this->plugin_name ),
				'id'            => 'wcdp_day_saturday',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => ''
			),
			array(
				'desc'          => __( 'Sunday', $this->plugin_name ),
				'id'            => 'wcdp_day_sunday',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => 'end'
			),
			array(
				'title'   => __( 'Limit to Categories', $this->plugin_name ),
				'desc'    => __( 'Would you like to limit to specific categories?', $this->plugin_name ),
				'type'    => 'checkbox',
				'default' => 'no',
				'id'      => 'wcdp_limit_to_cat'
			),
			array(
				'name'            => __('Active Categories', $this->plugin_name),
				'type'            => 'multiselect',
				'default'         => 'none',
				'options'         => $this->get_product_cat_values(),
				'id'              => 'wcdp_active_cats',
				'show_if_checked' => 'yes'
	        ),
			array(
				'type' 	=> 'sectionend',
				'id' 	=> 'wcdp_days_sectionend'
			),

			/**
			 * Weekly Product Reset Settings
			 */
			array(
				'title' => __( 'Weekly Reset', $this->plugin_name),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'wcdp_weekly_reset_title'
			),
			array(
				'title'   => __( 'Weekly reset', $this->plugin_name ),
				'desc'    => __( 'Enable weekly reset', $this->plugin_name ),
				'type'    => 'checkbox',
				'default' => 'no',
				'id'      => 'wcdp_weekly_reset_enabled'
	        ),
	        array(
				'desc'    => __( 'Day to reset', $this->plugin_name ),
				'type'    => 'select',
				'default' => 'Saturday',
				'options' => $this->get_day_options(),
				'id'      => 'wcdp_weekly_reset_day'
	        ),
	        array(
				'desc'    => __( 'Time to reset', $this->plugin_name ),
				'type'    => 'select',
				'default' => '00:00',
				'options' => $this->get_time_options(),
				'id'      => 'wcdp_weekly_reset_time'
	        ),
			array(
				'type' => 'sectionend',
				'id'   => 'wcdp_weekly_reset_sectionend'
			),

			/**
			 * Scheduled Stock Reset
			 */
			array(
				'title' => __( 'Daily Stock Reset', $this->plugin_name),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'wcdp_stock_reset_title'
			),
			array(
				'title'   => __( 'Stock reset', $this->plugin_name ),
				'desc'    => __( 'Enable daily stock reset', $this->plugin_name ),
				'type'    => 'checkbox',
				'default' => 'no',
				'id'      => 'wcdp_stock_reset_enabled'
	        ),
	        array(
				'name'            => __('Categories to reset', $this->plugin_name),
				'desc'            => __( '<b>Warning!</b> Make sure this is set correctly or you will reset the stock on the wrong products!' ),
				'type'            => 'multiselect',
				'default'         => 'null',
				'options'         => $this->get_product_cat_values(),
				'id'              => 'wcdp_stock_reset_cats',
				'show_if_checked' => 'yes'
	        ),
	        array(
				'desc'    => __( 'Time to reset stock', $this->plugin_name ),
				'type'    => 'select',
				'default' => '13:30',
				'options' => $this->get_time_options(),
				'id'      => 'wcdp_stock_reset_time'
	        ),
			array(
				'type' => 'sectionend',
				'id'   => 'wcdp_stock_reset_sectionend'
			),
		);
		return apply_filters( 'wc_settings_tab_wcdp_settings_tab', $settings );
	}

	// Update Woocommerce options
	public function update_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	// Things to do when settings are saved in the Settings Tab
	public function settings_saved() {
		if ( get_option( 'wcdp_weekly_reset_enabled' ) === 'yes' ) {
			$this->schedule_reset_day();
		} else {
			$this->clear_schedule_reset_day();
		}
	}

	
	/**
	 * Day Functions
	 */
	
	// Apply days to appropriate products
	public function apply_days_to_products( $post_id ) {
		
		// First remove day taxonomy from all products that have it
		// --------------------------------------------------------
		$this->reset_day_taxonomy();
		

		// Now apply the terms back onto the chosen products
		// -------------------------------------------------
		if ( 'product_settings' === $post_id && ! empty( $_POST['acf'] ) ) {
			foreach ($_POST['acf'] as $day => $products) {
				$term_slug = str_replace('field_', '', $day);
				foreach ($products as $product_id) {
					wp_set_object_terms($product_id, $term_slug, $this->category_slug, true);
				}
			}

		}
	}

	// Reset the Day taxonomy on all products
	public function reset_day_taxonomy() {
		// Get array of all the term ids
		$terms_array = $this->get_day_terms_id_array();
		
		// Create a custom loop of all the products that has any of
		// the terms
		$all_products = new WP_Query(array(
				'post_type' => 'product',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'taxonomy' => $this->category_slug,
						'field' => 'id',
						'terms' => $terms_array
					)
				)
			)
		);

		// Loop through and remove the terms from the post
		if ( $all_products->have_posts() ) {
			while ( $all_products->have_posts() ) {
				$all_products->the_post();
				$log = wp_remove_object_terms( get_the_id(), $terms_array, $this->category_slug );
			}
			wp_reset_postdata();
		}
	}

	// Schedule a reset of the day taxonomies
	public function schedule_reset_day() {
		if ( ! wp_next_scheduled( 'weekly_day_reset' ) ) {
			wp_schedule_event( strtotime( get_option( 'wcdp_weekly_reset_time' ) . ':00' ), 'daily', 'weekly_day_reset' );
		}
	}

	// Clear scheduled day taxonomy reset
	public function clear_schedule_reset_day() {
		if ( wp_next_scheduled( 'weekly_day_reset' ) ) {
			wp_clear_scheduled_hook( 'weekly_day_reset' );
		}
	}

	// If chosen day, reset days on all products
	public function weekly_day_reset() {
		if ( get_option( 'wcdp_weekly_reset_day' ) === date('l') ) {
			$this->reset_day_taxonomy();
		}
	}

	// Apply Days to products
	public function update_products( $post_id ) {
		$this->reset_day_taxonomy();
		$this->apply_days_to_products( $post_id );
	}

	/**
	 * Stock Functions
	 */

	// Reset the stock on all products with chosen class(es)
	public function reset_stock() {
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1
		);

		if ( $chosen_cats = get_option( 'wcdp_stock_reset_cats' ) ) {
			$terms_array = array();
			
			foreach ($chosen_cats as $cat) {
				$terms_array[] = $cat->term_id;
			}
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'id',
					'terms' => $terms_array
				)
			);
		}

		$all_products = new WP_Query($args);

		if ($all_products->have_posts()) {
			while ($all_products->have_posts()) {
				$all_products->the_post();
				$the_product = wc_get_product( get_the_id() );
				$the_product->set_stock(0);
			}
		}
	}

	// Schedule a stock reset
	public function schedule_reset_stock() {
		if ( ! wp_next_scheduled( 'daily_stock_reset' ) ) {
			wp_schedule_event( strtotime( get_option( 'wcdp_stock_reset_time' ) . ':00' ), 'hourly', 'daily_stock_reset' );
		}
	}

	// Clear the scheduled stock reset
	public function clear_schedule_reset_stock() {
		if ( wp_next_scheduled( 'daily_stock_reset' ) ) {
			wp_clear_scheduled_hook( 'daily_stock_reset' );
		}
	}

	// reset_stock wrapper
	public function daily_stock_reset() {
		$this->reset_stock();
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
