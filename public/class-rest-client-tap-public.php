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
     * Retrieve client/remote ip address
     *
     * @since 1.1.2
     *
     * @return string
     */
	private function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return $ip;
    }

    private function unset_errors() {
        if (array_key_exists('REST_CLIENT_TAP_ERRORS', $_SESSION)) {
            unset($_SESSION['REST_CLIENT_TAP_ERRORS']);
        }
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
     * @updated 1.1.4
     * @updated 1.1.5
     * @updated 1.1.8
     *
	 * @return null|string
	 */
	public function request_oauth_access_token()
	{
        $trasient_name = 'tap_oauth_client_access_token';
        $oauthAccessToken = get_transient($trasient_name);
		if($oauthAccessToken === false ) {
            $publicId = get_theme_mod('tap_public_id');
            $secretKey = get_theme_mod('tap_secret_key');
            if (empty($publicId) || empty($secretKey)) {
                $error = sprintf(__('[%s] No TAP PUBLIC ID or TAP SECRET KEY given. You must set TAP API access in <a href="%s">API REST</a> section',
                    $this->plugin_name), 'OAuth Credentials', esc_url(wp_customize_url()));
                do_action( 'qm/error', $error );
                add_settings_error('tap-troubleshoot', 'request-oauth-access-token', $error);
                delete_transient($trasient_name);
                return null;
            }

            $baseUrl = get_theme_mod('tap_base_url');
            $oauthUrl = sprintf($this->client_credentials_url, $baseUrl, $publicId, $secretKey);
            $oauthResponseBody = $this->get_result_from_api($oauthUrl, false, 'Access Token');
            if (empty($oauthResponseBody)) {
                delete_transient($trasient_name);
                return null;
            }
            if (!is_object($oauthResponseBody)) {
                $error = sprintf(__('[%s] Invalid OAuth response body', $this->plugin_name), 'OAuth Response');
                do_action( 'qm/error', $error );
                do_action( 'qm/debug', $oauthResponseBody );
                add_settings_error('tap-troubleshoot', 'request-oauth-access-token', $error);
                delete_transient($trasient_name);
                return null;
            }
            $oauthAccessToken = $oauthResponseBody->access_token;

            set_transient($trasient_name, $oauthAccessToken, (integer)$oauthResponseBody->expires_in);
        }

		return $oauthAccessToken;
	}
	
	/**
     * @updated 1.1.2
     * @updated 1.1.8
     *
	 * @param string $url
	 * @param bool $assoc
	 * @param string $intention
	 *
	 * @return null|array|object
	 */
	public function get_result_from_api($url, $assoc = true, $intention = 'Request result')
	{
        do_action( 'qm/info', 'Requesting result from api: {url}', ['url' => $url] );
        $apiResponse = wp_remote_get($url, array('timeout' => 35));
		$apiResponseCode = wp_remote_retrieve_response_code($apiResponse);
		$apiResponseBody = wp_remote_retrieve_body($apiResponse);
        do_action( 'qm/debug', $apiResponse );
		$error = null;
		if( is_wp_error( $apiResponse ) ) {
            $error = $apiResponse->get_error_message();
		} elseif( false === empty($apiResponseBody) && (int)$apiResponseCode >= 400 && (int)$apiResponseCode <= 499 ) {
            $errorResponse = json_decode($apiResponseBody, $assoc);
            $error = $assoc ? $errorResponse['error_description'] : $errorResponse->error_description;
		}
		
		if(!empty($error)){
			$error = sprintf( __( '[%s] Invalid response. %s', $this->plugin_name ), $intention, $error );
            do_action( 'qm/error', $error );
            add_settings_error('tap-troubleshoot', 'result-from-api', $error);
			return null;
		}

		return json_decode($apiResponseBody, $assoc);
	}

    /**
     * @since 1.1.3
     * @updated 1.1.8
     *
     * @return null|string
     */
	private function get_oauth_access_token() {
	    $max_retries = 3;
        $oauthAccessToken = null;
        while (empty($oauthAccessToken) && $max_retries > 0) {
            $oauthAccessToken = $this->request_oauth_access_token();
            $max_retries--;
        }

        if(empty($oauthAccessToken) && $max_retries === 0) {
            $error = __( '[%s] Max retries reached requesting oauth access token.', $this->plugin_name );
            do_action( 'qm/error', $error );
            add_settings_error('tap-troubleshoot', 'oauth-access-token', $error);
        }

        return $oauthAccessToken;
    }

    /**
     * @updated 1.1.2
     * @updated 1.1.5
     * @updated 1.1.8
     */
	public function request_bookies()
	{
        $trasient_name = 'tap_bookies';
        $result_from_api = get_transient($trasient_name);
        if($result_from_api === false) {
            $url_sync_link_bookies = '%s/api/blocks-bookies/%s/%s/listado-bonos-bookies.json/?access_token=%s&_=%s';
            $baseUrl = get_theme_mod('tap_base_url');
            $tracked_web_category = get_theme_mod('tap_tracker_web_category');
            $traker = get_theme_mod('tap_tracker_domain');

            $oauthAccessToken = $this->get_oauth_access_token();

            if (empty($oauthAccessToken)) {
                delete_transient($trasient_name);
                return;
            }

            $now = new \DateTime('now');

            $apiUrl = esc_url(sprintf($url_sync_link_bookies, $baseUrl, $tracked_web_category, $traker,
                $oauthAccessToken, $now->getTimestamp()));
            $result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Bookies');
            if(empty($result_from_api)) {
                return;
            }

            if(false === is_array($result_from_api)) {
                return;
            }

            update_option('TAP_BOOKIES', $result_from_api);
            set_transient($trasient_name, $result_from_api, 4 * 3600);
        }
	}

    /**
     * @updated 1.1.2
     * @updated 1.1.5
     * @updated 1.1.8
     */
	public function request_sports()
	{
        $trasient_name = 'tap_deportes';
        $result_from_api = get_transient($trasient_name);
        if($result_from_api === false) {
            $url_sync_link_deportes = '%s/api/deporte/listado-visible-blogs.json/?access_token=%s&_=%s';
            $baseUrl = get_theme_mod('tap_base_url');

            $oauthAccessToken = $this->get_oauth_access_token();

            if (empty($oauthAccessToken)) {
                return;
            }

            $now = new \DateTime('now');

            $apiUrl = esc_url(sprintf($url_sync_link_deportes, $baseUrl, $oauthAccessToken, $now->getTimestamp()));
            $result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Sports');
            if(empty($result_from_api)) {
                return;
            }

            if(false === array_key_exists('deporte', $result_from_api)) {
                return;
            }

            if (array_key_exists('deporte', $result_from_api) && empty($result_from_api['deporte'])) {
               return;
            }

            update_option('TAP_DEPORTES', $result_from_api['deporte']);
            set_transient($trasient_name, $result_from_api['deporte'], 4 * 3600);
        }
	}

    /**
     * @updated 1.1.2
     * @updated 1.1.5
     * @updated 1.1.8
     */
	public function request_competitions()
	{
        $trasient_name = 'tap_competiciones';
        $result_from_api = get_transient($trasient_name);
        if($result_from_api === false) {
            $url_sync_link_competiciones = '%s/api/competicion/listado.json/?access_token=%s&_=%s';
            $baseUrl = get_theme_mod('tap_base_url');

            $oauthAccessToken = $this->get_oauth_access_token();

            if (empty($oauthAccessToken)) {
                return;
            }

            $now = new \DateTime('now');

            $apiUrl = esc_url(sprintf($url_sync_link_competiciones, $baseUrl, $oauthAccessToken, $now->getTimestamp()));
            $result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Competitions');
            if(empty($result_from_api)) {
                return;
            }

            if(false === array_key_exists('competicion', $result_from_api)) {
                return;
            }

            if (array_key_exists('competicion', $result_from_api) && empty($result_from_api['competicion'])) {
                return;
            }

            update_option('TAP_COMPETICIONES', $result_from_api['competicion']);
            set_transient($trasient_name, $result_from_api['competicion'], 4 * 3600);
        }
	}

    /**
     * @updated 1.1.2
     * @updated 1.1.5
     * @updated 1.1.8
     *
     * @param $track_site
     * @param $tracked_web_category
     * @return array|mixed|object|null
     */
	public function request_block_bookies($track_site, $tracked_web_category)
	{
        $clientIp = $this->get_client_ip();
        $trasient_name = 'tap_blocks_bookies_' . implode('_', explode('.', $clientIp, -1 ));
        $result_from_api = get_transient($trasient_name);
        if($result_from_api === false) {
            $url_block_bookies = '%s/api/blocks-bookies/%s/%s/listado.%s/%s/?access_token=%s&_=%s';
            $baseUrl = get_theme_mod('tap_base_url');

            $oauthAccessToken = $this->get_oauth_access_token();

            if (empty($oauthAccessToken)) {
                return [];
            }

            $now = new \DateTime('now');

            $apiUrl = esc_url(sprintf($url_block_bookies, $baseUrl, $tracked_web_category, $track_site, 'json',
                $clientIp, $oauthAccessToken, $now->getTimestamp()));
            $result_from_api = $this->get_result_from_api($apiUrl, true, 'Request Block Bookies');

            set_transient($trasient_name, $result_from_api, 24 * 3600);
        }

		return $result_from_api;
	}

    /**
     * @updated 1.1.2
     * @updated 1.1.5
     * @updated 1.1.8
     *
     * @param $session_name
     * @param null $ip
     * @return array|mixed|object|null
     */
	public function check_ip($session_name, $ip = null)
	{
        if(is_null($ip)) {
            $ip = $this->get_client_ip();
        }

        $trasient_name = 'tap_check_ip_' . $ip;
        $result = get_transient($trasient_name);
        if($result === false) {

            if (isset($_SESSION) && array_key_exists($session_name, $_SESSION)) {
                if (strcmp($ip, $_SESSION[$session_name]['client_ip']) === 0) {
                    return $_SESSION[$session_name]['client_country'];
                }
                unset($_SESSION[$session_name]);
            }

            $url_check_ip = '%s/api/geoip/country-by-ip.json/%s/?access_token=%s&_=%s';
            $baseUrl = get_theme_mod('tap_base_url');

            $oauthAccessToken = $this->get_oauth_access_token();
            if (empty($oauthAccessToken)) {
                return null;
            }

            $now = new \DateTime('now');

            $apiUrl = esc_url(sprintf($url_check_ip, $baseUrl, $ip, $oauthAccessToken, $now->getTimestamp()));
            $result = $this->get_result_from_api($apiUrl, true, 'Request Country by IP');

            set_transient($trasient_name, $result, 24 * 3600);
        }

        return $result;
	}
}
