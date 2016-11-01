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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Add new settings tab in Woocommerce > Settings
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['wcdp_settings_tab'] = __('First Order Discount', $this->plugin_name);
		return $settings_tabs;
	}

	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	public function get_settings() {
		$settings = array(
			'general_section_title' => array(
				'name' 		=> 'General Options',
				'type' 		=> 'title',
				'id' 		=> 'wcdp_settings_tab_general_title'
			),
			'enable_plugin' => array(
				'name' 		=> __( 'Enable First Order Discount', $this->plugin_name ),
				'type' 		=> 'checkbox',
				'default' 	=> 'no',
				'id' 		=> 'wcdp_settings_tab_enable'
			),
			'general_section_end' => array(
				'type' 		=> 'sectionend',
				'id' 		=> 'wcdp_settings_tab_general_end'
			),
			'popup_section_title' => array(
				'name'		=> __( 'Popup Modal', $this->plugin_name ),
				'type'		=> 'title',
				'id'		=> 'wcdp_settings_tab_popup_section_title'
			),
			'popup_content'	=> array(
				'name'		=> __( 'Modal Content Editor', $this->plugin_name ),
				'type'		=> 'wpeditor',
				'id'		=> 'wcdp_settings_tab_popup_popup_content'
			),
			'popup_section_end' => array(
				'type' 		=> 'sectionend',
				'id' 		=> 'wcdp_settings_tab_popup_section_end'
			)
		);
		return apply_filters( 'wc_settings_tab_wcdp_settings_tab', $settings );;
	}

	public function update_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	public function display_editor( $value ) {
		$option_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] ); ?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<?php echo $value['desc']; ?>
				<?php wp_editor( $option_value, esc_attr( $value['id'] ) ); ?>
			</td>
		</tr>
	<?php
	}

	public function save_editor_val( $value ) {
		$email_text = $_POST[$value['id']];
		update_option( $value['id'], $email_text  );
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
