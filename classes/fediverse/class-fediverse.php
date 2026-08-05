<?php
/**
 * Fediverse hovercards module.
 *
 * @package Automattic\Gravatar\GravatarEnhanced
 */

namespace Automattic\Gravatar\GravatarEnhanced\Fediverse;

use Automattic\Gravatar\GravatarEnhanced\Module;
use WP_Error;

/**
 * Fediverse hovercards module.
 */
class Fediverse implements Module {
	const OPTION_FEDIVERSE_HOVERCARDS = 'gravatar_fediverse_hovercards';

	const FILTER_FEDIVERSE_MODULE_ENABLED = 'gravatar_enhanced_fediverse_module_enabled';

	const REST_NAMESPACE = 'gravatar-enhanced/v1';

	const REST_ROUTE_LOOKUP = '/fediverse/lookup';

	/**
	 * Initialize the module.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'maybe_load' ) );
	}

	/**
	 * Load the module if enabled.
	 *
	 * @return void
	 */
	public function maybe_load() {
		if ( $this->is_module_disabled() ) {
			return;
		}

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Uninstall the module.
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( self::OPTION_FEDIVERSE_HOVERCARDS );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE_LOOKUP,
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_lookup' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'url' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
						'validate_callback' => array( $this, 'validate_url' ),
					),
				),
			)
		);
	}

	/**
	 * Validate the URL parameter.
	 *
	 * @param mixed            $value The value to validate.
	 * @param \WP_REST_Request $request The request object.
	 * @param string           $param The parameter name.
	 * @return true|\WP_Error
	 */
	public function validate_url( $value, $request, $param ) { // phpcs:ignore Generic.Commenting.DocComment.TagInvalid
		if ( ! is_string( $value ) || empty( $value ) ) {
			return new \WP_Error( 'rest_invalid_param', __( 'URL is required.', 'gravatar-enhanced' ), array( 'status' => 400 ) );
		}

		$url = esc_url_raw( $value );
		if ( empty( $url ) || ! wp_http_validate_url( $url ) ) {
			return new \WP_Error( 'rest_invalid_param', __( 'Invalid URL.', 'gravatar-enhanced' ), array( 'status' => 400 ) );
		}

		$parsed = wp_parse_url( $url );
		if ( empty( $parsed['host'] ) ) {
			return new \WP_Error( 'rest_invalid_param', __( 'URL must contain a host.', 'gravatar-enhanced' ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * Handle the lookup request.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_lookup( $request ) { // phpcs:ignore Generic.Commenting.DocComment.TagInvalid
		$url = $request->get_param( 'url' );

		$cache_key = 'gravatar_fediverse_' . md5( $url );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		$resolver = new Fediverse_Resolver();
		$profile  = $resolver->resolve( $url );

		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		set_transient( $cache_key, $profile, HOUR_IN_SECONDS );

		return rest_ensure_response( $profile );
	}

	/**
	 * Check if the module is disabled.
	 *
	 * @return bool
	 */
	private function is_module_disabled() {
		if ( ! apply_filters( self::FILTER_FEDIVERSE_MODULE_ENABLED, true ) ) {
			return true;
		}

		if ( ! $this->is_option_enabled() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the option is enabled.
	 *
	 * @return bool
	 */
	private function is_option_enabled() {
		return boolval( get_option( self::OPTION_FEDIVERSE_HOVERCARDS, true ) );
	}
}
