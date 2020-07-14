<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.linkedin.com/in/mrbrazzi/
 * @since      1.0.0
 *
 * @package    RestClientTap
 * @subpackage RestClientTap/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    RestClientTap
 * @subpackage RestClientTap/admin
 * @author     Alain Sanchez <luka.ghost@gmail.com>
 */
class RestClientTapAdmin {

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in RestClientTapLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The RestClientTapLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rest-client-tap-admin.css', array(), $this->version, 'all' );

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
		 * defined in RestClientTapLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The RestClientTapLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rest-client-tap-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function custom_options( \WP_Customize_Manager $wp_customize ) {
		// API REST
		$wp_customize->add_section( 'tap_api_rest', array(
			'title'       => __( 'API REST', $this->plugin_name ),
			'description' => '',
			'priority'    => 0
		) );
		
		$wp_customize->add_setting( 'tap_base_url', array(
			'default'           => 'https://todoapuestas.com',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
            'dirty'             => true,
		) );
		
		$wp_customize->add_control( 'tap_base_url', array(
			'type'        => 'text',
			'label'       => __( 'Base url', $this->plugin_name ),
			'description' => __( 'Write the base url where the API Rest is hosted.', $this->plugin_name ),
			'section'     => 'tap_api_rest',
			'settings'    => 'tap_base_url',
		) );
		
		$wp_customize->add_setting( 'tap_public_id', array(
			'default'           => null,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		
		$wp_customize->add_control( 'tap_public_id', array(
			'type'        => 'text',
			'label'       => __( 'Public ID TAP', $this->plugin_name ),
			'description' => __( 'Write the Public ID for this domain witch can access to API Rest services', $this->plugin_name ),
			'section'     => 'tap_api_rest',
			'settings'    => 'tap_public_id',
		) );
		
		$wp_customize->add_setting( 'tap_secret_key', array(
			'default'           => null,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		
		$wp_customize->add_control( 'tap_secret_key', array(
			'type'        => 'text',
			'label'       => __( 'Secret Key TAP', $this->plugin_name ),
			'description' => __( 'Write the Secret Key for this domain witch can access to API Rest services', $this->plugin_name ),
			'section'     => 'tap_api_rest',
			'settings'    => 'tap_secret_key',
		) );
		
		$wp_customize->add_setting( 'tap_tracker_web_category', array(
			'default'           => 'apuestas',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		
		$wp_customize->add_control( 'tap_tracker_web_category', array(
			'type'        => 'select',
			'label'       => __( 'Tracker Web Category', $this->plugin_name ),
			'description' => __( 'Select tracker web category for this website', $this->plugin_name ),
			'section'     => 'tap_api_rest',
			'settings'    => 'tap_tracker_web_category',
			'choices'     => array(
				'apuestas' => __( 'Pick', $this->plugin_name ),
				'bingo'    => __( 'Bingo', $this->plugin_name ),
				'casinos'  => __( 'Casino', $this->plugin_name ),
				'juegos'   => __( 'Game', $this->plugin_name ),
				'poker'    => __( 'Poker', $this->plugin_name ),
			),
		) );
		
		$wp_customize->add_setting( 'tap_tracker_domain', array(
			'default'           => $_SERVER['HTTP_HOST'],
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		
		$wp_customize->add_control( 'tap_tracker_domain', array(
			'type'        => 'text',
			'label'       => __( 'Domain to Track', $this->plugin_name ),
			'description' => __( 'Write the domain name to track', $this->plugin_name ),
			'section'     => 'tap_api_rest',
			'settings'    => 'tap_tracker_domain',
		) );
	}

}
