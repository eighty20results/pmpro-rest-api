<?php
/*
Plugin Name: Eighty/20 Results - New Plugin Framework for Paid Memberships Pro
Plugin URI: https://eighty20results.com/wordpress-plugins/new-plugin-framework/
Description: TODO - Enter description here
Version: 1.0
Author: Eighty / 20 Results by Wicked Strong Chicks, LLC <thomas@eighty20results.com>
Author URI: https://eighty20results.com/thomas-sjolshagen/
Text Domain: TOD: Enter text domain here
Domain Path: /languages
License:

	Copyright 2016 - Eighty / 20 Results by Wicked Strong Chicks, LLC (thomas@eighty20results.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

defined( 'ABSPATH' ) || die( 'Cannot access plugin sources directly' );
define( 'E20R_PMPRORESTAPI_VER', '1.0' );

class pmproRestAPI extends WP_REST_Controller {

	/**
	 * @var pmproRestAPI $instance The class instance
	 */
	static $instance = null;

	/**
	 * @var string $option_name The name to use in the WordPress options table
	 */
	private $option_name;

	/**
	 * @var array $options Array of levels with setup fee values.
	 */
	private $options;

	/**
	 * @var bool $loged_in
	 */
	private $logged_in;

	/**
	 * @var WP_User $user WP_User object for the (logged in) user.
	 */
	private $user;

	/**
	 * pmproRestAPI constructor.
	 */
	public function __construct() {

		$this->option_name = strtolower( get_class( $this ) );

		add_action( 'plugins_loaded', array( $this, 'addRoutes' ) );
		add_action( 'http_api_curl', array( $this, 'force_tls_12' ) );
	}

	/**
	 * Load all REST API endpoints for the PMPro add-on
	 */
	public function addRoutes() {

		// FIXME: Route definition for the levels array (if applicable)
		add_action( 'rest_api_init', function () {
			register_rest_route( 'pmpro/v1', '/hasaccess/post=(?P<post>\d+)/user=(?P<user>\d+)/levels=(?P<levels>\d+)', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'checkAccess' ),
				'args'     => array(
					'post'   => array(
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						}
					),
					'user'   => array(
						'validate_callback' => function ( $param, $request, $key ) {


							return ( empty( $param ) || is_numeric( $param ) );
						}
					),
					'levels' => array(
						'validate_callback' => function ( $param, $request, $key ) {

							// FIXME: Validation callback for the levels array (not an array atm)

							// Check that all of the values supplied are numbers
							$level_ints = array_map( 'is_numeric', $param );

							// Return true if there are no instances of 'false' in the is_numeric check.
							return ( empty( $param ) || ! in_array( false, $level_ints ) );
						}
					)
				),
			) );
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'pmpro/v1', '/getlevelforuser/(?P<user>[0-9a-zA-Z\-]+)', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'getLevelForUser' ),
				'args'     => array(
					'user' => array(
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						}
					),
				),
			) );
		} );
	}

	/**
	 * REST API endpoint handler for PMPro post/page access check
	 *
	 * @param $request
	 *
	 * @return array|bool|mixed|void|WP_Error
	 */
	public function checkAccess( $request ) {

		global $current_user;

		$this->logged_in = is_user_logged_in();
		$this->user      = $current_user;

		if ( false === $this->logged_in || false === current_user_can( 'manage_options' ) ) {

			return new WP_Error( 'pmpro_rest_access', __( 'User does not have access to the PMPro REST API', 'pmpro' ) );
		}

		$post_id                  = $request['post'];    //post id to check
		$user_id                  = $request['user'];    //optional user id passed in
		$return_membership_levels = $request['levels'];    //option to also include an array of membership levels with access to the post

		return pmpro_has_membership_access( $post_id, $user_id, $return_membership_levels );
	}

	/**
	 *
	 * Return the PMPro Membership Level for the specified user ID
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function getLevelForUser( $request ) {

		global $current_user;

		$this->logged_in = is_user_logged_in();
		$this->user      = $current_user;

		if ( false === $this->logged_in || false === current_user_can( 'manage_options' ) ) {

			return new WP_Error( 'pmpro_rest_access', __( 'User does not have access to the PMPro REST API', 'pmpro' ) );
		}

		$user_id = $request['user'];

		return pmpro_getMembershipLevelForUser( $user_id );
	}

	/**
	 * Retrieve and initiate the class instance
	 *
	 * @return pmproRestAPI
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}

		$class = self::$instance;

		return $class;
	}

	/**
	 * Load the required translation file for the add-on
	 */
	public function loadTranslation() {

		$locale = apply_filters( "plugin_locale", get_locale(), "e20rrapi" );
		$mo     = "e20rrapi-{$locale}.mo";

		// Paths to local (plugin) and global (WP) language files
		$local_mo  = plugin_dir_path( __FILE__ ) . "/languages/{$mo}";
		$global_mo = WP_LANG_DIR . "/e20rrapi/{$mo}";

		// Load global version first
		load_textdomain( "e20rrapi", $global_mo );

		// Load local version second
		load_textdomain( "e20rrapi", $local_mo );
	}

	/**
	 * Connect to the license server using TLS 1.2
	 *
	 * @param $handle - File handle for the pipe to the CURL process
	 */
	public function force_tls_12( $handle ) {

		// set the CURL option to use.
		curl_setopt( $handle, CURLOPT_SSLVERSION, 6 );
	}

	/**
	 * Autoloader class for the plugin.
	 *
	 * @param string $class_name Name of the class being attempted loaded.
	 */
	public function __class_loader( $class_name ) {

		$classes = array(
			strtolower( get_class( $this ) ),
		);

		$plugin_classes = $classes;

		if ( in_array( strtolower( $class_name ), $plugin_classes ) && ! class_exists( $class_name ) ) {

			$name = strtolower( $class_name );

			$filename = dirname( __FILE__ ) . "/classes/class.{$name}.php";

			if ( file_exists( $filename ) ) {
				require_once $filename;
			}

		}
	} // End of autoloader method
}

/**
 * Configure autoloader
 */
spl_autoload_register( array( pmproRestAPI::get_instance(), '__class_loader' ) );
add_action( 'plugins_loaded', 'pmproRestAPI::get_instance' );

/**
 * Configure one-click update
 */
if ( ! class_exists( '\\PucFactory' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'plugin-updates/plugin-update-checker.php' );
}

$plugin_updates = \PucFactory::buildUpdateChecker(
	'https://eighty20results.com/protected-content/pmpro-rest-api/metadata.json',
	__FILE__,
	'pmpro-rest-api'
);
