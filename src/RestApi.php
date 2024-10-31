<?php

namespace BM\PopUpBuilder;

class RestApi
{
	public function __construct()
	{
		add_action( 'rest_api_init', [$this, 'rest_api_init'] );
		@error_reporting(0);
	}

	public function rest_api_init()
	{
		register_rest_route( 'bm/puba', '/health-check', array(
			'methods' => ['POST', 'GET'],
			'callback' => [$this, 'health_check'],
			'permission_callback' => function(){
				return current_user_can( 'manage_options' );
			}
		));

		register_rest_route( 'bm/puba', '/update-settings', array(
			'methods' => ['POST', 'GET'],
			'callback' => [$this, 'update_settings'],
			'permission_callback' => function(){
				return current_user_can( 'manage_options' );
			}
		));

		register_rest_route( 'bm/puba', '/reconnect', array(
			'methods' => ['POST', 'GET'],
			'callback' => [$this, 'reconnect'],
			'permission_callback' => function(){
				return current_user_can( 'manage_options' );
			}
		));
	}

	public function reconnect( \WP_REST_Request $request ): array
	{
		if( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return [
				'status'    => 403,
				'message'   => esc_attr__('Access forbidden', 'popupbuilder-app'),
			];
		}

		$delete_keys = ['api_key', 'campaign', 'notifications'];
		foreach ($delete_keys as $key) {
			Settings::delete_setting($key);
		}

		return [
			'status'    => 200,
			'message'   => esc_attr__('Settings saved', 'popupbuilder-app'),
		];
	}

	public function update_settings( \WP_REST_Request $request ): array
	{
		if( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return [
				'status'    => 403,
				'message'   => esc_attr__('Access forbidden', 'popupbuilder-app'),
			];
		}

		$auto_embed_pixel = isset($_POST['auto_embed_pixel']) ? sanitize_text_field($_POST['auto_embed_pixel']) : false;

		if( !empty($auto_embed_pixel) && in_array($auto_embed_pixel, ['on', 'off']) ) {
			Settings::set_setting('auto_embed_pixel', $auto_embed_pixel);
		}

		$allowed_settings = ['woocommerce_orders'];

		foreach ($allowed_settings as $setting_key) {
			$setting = isset($_POST[$setting_key]) ? rest_sanitize_array($_POST[$setting_key]) : false;

			if( is_array($setting) && count($setting) >= 1 ) {
				Settings::set_setting($setting_key, $setting);
			} else {
				Settings::delete_setting($setting_key);
			}
		}

		return [
			'status'    => 200,
			'message'   => esc_attr__('Settings saved', 'popupbuilder-app')
		];
	}

	public function health_check( \WP_REST_Request $request ): array
	{
		if( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return [
				'status'    => 403,
				'color'     => 'red',
				'message'   => esc_attr__('Access forbidden', 'popupbuilder-app'),
			];
		}

		$api_key        = Settings::get_setting('api_key');
		$campaign       = Settings::get_setting('campaign');
		$client         = new ApiClient($api_key);
		$notifications  = $client->get_notifications();

		if( $client->is_good() && is_array($notifications) ) {
			$old_notifications = Settings::get_setting('notifications', false);

			$diff1 = count($old_notifications);
			$diff2 = count($notifications);

			Settings::set_setting('notifications', $notifications);

			if( $diff1 !== $diff2 ) {
				return [
					'status'    => 200,
					'color'     => 'yellow',
					'message'   => esc_attr__('Found new notifications, please refresh this page!', 'popupbuilder-app'),
					'action'    => 'refresh'
				];
			}
		} else {
			return [
				'status'    => 403,
				'color'     => 'red',
				'message'   => esc_attr__('API is not reachable or your key is wrong', 'popupbuilder-app')
			];
		}

		if( ! $campaign->is_enabled ) {
			return [
				'status'    => 200,
				'color'     => 'yellow',
				'message'   => esc_attr__('Campaign is disabled and will not deliver your content!', 'popupbuilder-app')
			];
		}

		if( ! strstr(home_url('/'), $campaign->domain) ) {
			return [
				'status'    => 200,
				'color'     => 'yellow',
				'message'   => esc_attr__('Domain mismatch: Campaign domain does not match website domain', 'popupbuilder-app')
			];
		}

		return [
			'status'    => 200,
			'color'     => 'green',
			'message'   => esc_attr__('Everything is fine', 'popupbuilder-app')
		];
	}
}