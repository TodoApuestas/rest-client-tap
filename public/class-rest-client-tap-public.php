<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.linkedin.com/in/mrbrazzi/
 * @since      1.0.0
 *
 * @package    RestClientTap
 * @subpackage RestClientTap/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    RestClientTap
 * @subpackage RestClientTap/public
 * @author     Alain Sanchez <luka.ghost@gmail.com>
 */
class RestClientTapPublic {

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
	 * The url to access Rest API
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string     $client_credentials_url
	 */
	private $client_credentials_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		$this->client_credentials_url = '%s/oauth/v2/token?client_id=%s&client_secret=%s&grant_type=client_credentials&scope=api';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rest-client-tap-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rest-client-tap-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * @since 1.0.0
	 *
	 * @return null|string
	 */
	public function get_oauth_access_token()
	{
		$session_id = session_id();
		if(empty($session_id) && !headers_sent()){
			@session_start();
		}
		
		if(isset($_SESSION['TAP_OAUTH_CLIENT'])){
			$now = new \DateTime('now');
			if($now->getTimestamp() <= (integer)$_SESSION['TAP_OAUTH_CLIENT']['expires_in']){
				$oauthAccessToken = $_SESSION['TAP_OAUTH_CLIENT']['access_token'];
				return $oauthAccessToken;
			}
			unset($_SESSION['TAP_OAUTH_CLIENT']);
		}
		
		$publicId = get_theme_mod( 'tap_public_id' );
		$secretKey = get_theme_mod( 'tap_secret_key' );
		if(empty($publicId) || empty($secretKey)){
			$error = sprintf(__('[%s] No TAP PUBLIC ID or TAP SECRET KEY given. You must set TAP API access in <a href="%s">API REST</a> section', $this->plugin_name), 'OAuth Credentials', esc_url(wp_customize_url()));
			$_SESSION['REST_CLIENT_TAP_ERRORS'][] = $error;
			log_error($error);
			return null;
		}
		
		$baseUrl = get_theme_mod( 'tap_base_url' );
		$oauthUrl = sprintf($this->client_credentials_url, $baseUrl, $publicId, $secretKey);
		$oauthResponseBody = $this->get_result_from_api($oauthUrl, false, 'Access Token');
		if(!is_object($oauthResponseBody)){
			$error = sprintf(__('[%s] Invalid OAuth response body', $this->plugin_name), 'OAuth Response');
			$_SESSION['REST_CLIENT_TAP_ERRORS'][] = $error;
			log_error($error);
			return null;
		}
		$oauthAccessToken = $oauthResponseBody->access_token;
		
		if(!isset($_SESSION['TAP_OAUTH_CLIENT'])){
			$now = new \DateTime('now');
			$_SESSION['TAP_OAUTH_CLIENT'] = array(
				'access_token' => $oauthAccessToken,
				'expires_in' => $now->getTimestamp() + (integer)$oauthResponseBody->expires_in
			);
		}
		
		return $oauthAccessToken;
	}
	
	/**
	 * @param string $url
	 * @param bool $assoc
	 * @param string $intention
	 *
	 * @return null|array|object
	 */
	public function get_result_from_api($url, $assoc = true, $intention = 'Request result')
	{
		$apiResponse = wp_remote_get($url);
		$apiResponseCode = wp_remote_retrieve_response_code($apiResponse);
		$apiResponseBody = wp_remote_retrieve_body($apiResponse);
		
		$error = null;
		if( is_wp_error( $apiResponse ) ) {
			$error = $apiResponse->get_error_message();
		} elseif( '' !== $apiResponseBody && isset($apiResponseBody['error']) && !empty($apiResponseBody['error']) && strcmp( $apiResponseCode, '200' ) === 0 ){
			$error = $apiResponseBody['error_description'];
		}
		
		if(null !== $error){
			$error = sprintf( __( '[%s] Invalid response. %s', $this->plugin_name ), $intention, $error );
			$_SESSION['REST_CLIENT_TAP_ERRORS'][] = $error;
			log_error($error);
			return null;
		}
		
		return json_decode($apiResponseBody, $assoc);
	}
	
	public function request_bookies()
	{
		$url_sync_link_bookies = '%s/api/blocks-bookies/%s/%s/listado-bonos-bookies.json/?access_token=%s&_=%s';
		$baseUrl = get_theme_mod( 'tap_base_url' );
		$tracked_web_category = get_theme_mod('tap_tracker_web_category');
		$traker = get_theme_mod('tap_tracker_domain');
		$oauthAccessToken = $this->get_oauth_access_token();
		$now = new \DateTime('now');
		
		$apiUrl = esc_url(sprintf($url_sync_link_bookies, $baseUrl, $tracked_web_category, $traker, $oauthAccessToken, $now->getTimestamp()));
		$result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Bookies');
		if(null !== $result_from_api && count($result_from_api)){
			update_option('TAP_BOOKIES', $result_from_api);
		}
	}
	
	public function request_sports()
	{
		$url_sync_link_deportes = '%s/api/deporte/listado-visible-blogs.json/?access_token=%s&_=%s';
		$baseUrl = get_theme_mod( 'tap_base_url' );
		$oauthAccessToken = $this->get_oauth_access_token();
		$now = new \DateTime('now');
		
		$apiUrl = esc_url(sprintf($url_sync_link_deportes, $baseUrl, $oauthAccessToken, $now->getTimestamp()));
		$result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Sports');
		if(isset($result_from_api['deporte']) && count($result_from_api['deporte'])){
			update_option('TAP_DEPORTES', $result_from_api['deporte']);
		}
	}
	
	public function request_competitions()
	{
		$url_sync_link_competiciones = '%s/api/competicion/listado.json/?access_token=%s&_=%s';
		$baseUrl = get_theme_mod( 'tap_base_url' );
		$oauthAccessToken = $this->get_oauth_access_token();
		$now = new \DateTime('now');
		
		$apiUrl = esc_url(sprintf($url_sync_link_competiciones, $baseUrl, $oauthAccessToken, $now->getTimestamp()));
		$result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Competitions');
		if(isset($result_from_api['competicion']) && count($result_from_api['competicion'])){
			update_option('TAP_COMPETICIONES', $result_from_api['competicion']);
		}
	}
	
	public function request_block_bookies($track_site)
	{
		$url_block_bookies = '%s/api/blocks-bookies/apuestas/%s/listado.%s/%s/?access_token=%s&_=%s';
		$baseUrl = get_theme_mod( 'tap_base_url' );
		$clientIp = $_SERVER['REMOTE_ADDR'];
		$oauthAccessToken = $this->get_oauth_access_token();
		$now = new \DateTime('now');
		
		$apiUrl = esc_url(sprintf($url_block_bookies, $baseUrl, $track_site, 'json', $clientIp, $oauthAccessToken, $now->getTimestamp()));
		$result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Block Bookies');
		
		return $result_from_api;
	}
	
	public function check_ip($ip)
	{
		$url_check_ip = '%s/api/geoip/country-by-ip.json/%s/?access_token=%s&_=%s';
		$baseUrl = get_theme_mod( 'tap_base_url' );
		$oauthAccessToken = $this->get_oauth_access_token();
		$now = new \DateTime('now');
		
		$apiUrl = esc_url(sprintf($url_check_ip, $baseUrl, $ip, $oauthAccessToken, $now->getTimestamp()));
		$result = $this->get_result_from_api($apiUrl, true, 'Request Country by IP');
		// TODO: revisar funcionamiento.
		return $result;
	}
}
