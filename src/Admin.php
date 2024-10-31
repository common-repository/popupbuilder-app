<?php

namespace BM\PopUpBuilder;

/* Security-Check */
if (!class_exists('WP')) {
	die();
}

class Admin
{
	public function __construct()
	{
		add_action( 'admin_menu',               [$this, 'register_submenu'] );
		add_action( 'admin_enqueue_scripts',    [$this, 'admin_enqueue_scripts'] );
	}

	public function admin_enqueue_scripts(): void
	{
		$screen     = get_current_screen();
		$version    = PUBA_VERSION;
		//$version    = time(); // Only for Development

		if( isset($screen->id) && $screen->id === 'settings_page_popupbuilderapp' ) {
			wp_enqueue_style( 'bmpuba-swal', plugins_url('/assets/css/sweetalert2.css', PUBA_FILE), null, '11.4.8');
			wp_enqueue_style( 'bmpuba', plugins_url('/assets/css/admin.css', PUBA_FILE), null, $version);

			wp_register_script( 'bmpuba-swal', plugins_url('/assets/js/sweetalert2.js', PUBA_FILE), ['jquery'], '11.4.8');
			wp_register_script( 'bmpuba', plugins_url('/assets/js/admin.js', PUBA_FILE), ['jquery', 'bmpuba-swal'], $version);
			wp_localize_script( 'bmpuba', 'bmpubaApiSettings', array(
				'health_check'          => esc_url_raw( rest_url('bm/puba/health-check') ),
				'update_settings'       => esc_url_raw( rest_url('bm/puba/update-settings') ),
				'reconnect'             => esc_url_raw( rest_url('bm/puba/reconnect') ),
				'nonce'                 => wp_create_nonce( 'wp_rest' ),
				'msg_refresh'           => __('Refresh now', 'popupbuilder-app'),
				'msg_ignore'            => __('Ignore', 'popupbuilder-app'),
				'msg_saved'             => __('Settings Saved', 'popupbuilder-app'),
				'msg_reconnect_title'   => __('Do you really want to disconnect the plugin and reset its settings?', 'popupbuilder-app'),
				'msg_reconnect_yes'     => __('Reset Connection', 'popupbuilder-app'),
				'msg_reconnect_no'      => __('Discard', 'popupbuilder-app'),
			) );
			wp_enqueue_script( 'bmpuba' );
		}
	}

	public function register_submenu(): void
	{
		add_submenu_page(
			'options-general.php',
			__( 'PopUpBuilder.App', 'popupbuilder-app' ),
			__( 'PopUpBuilder.App', 'popupbuilder-app' ),
			'manage_options',
			'popupbuilderapp',
			[$this, 'admin_options']
		);
	}

	public function admin_options(): void
	{
		//delete_option('puba_api_key');
		//delete_option('puba_campaign');

		$api_key    = Settings::get_setting('api_key');
		$campaign   = Settings::get_setting('campaign');

		if( ! empty($api_key) && ! empty($campaign) ) {
			$this->overview();
		} else {
			$this->setup_process($api_key);
		}
	}

	private function overview()
	{
		$notifications   = Settings::get_setting('notifications');

		if( empty($notifications) ) {
			$api_key        = Settings::get_setting('api_key');
			$client         = new ApiClient($api_key);
			$notifications  = $client->get_notifications();

			if( $client->is_good() && is_array($notifications) ) {
				Settings::set_setting('notifications', $notifications);
			} else {
				$notifications = false;
			}
		}

		echo $this->load_template('admin-overview', [
			'api_key'       => $api_key ?? false,
			'client'        => $client ?? false,
			'notifications' => $notifications ?? false
		]);
	}

	private function setup_process($api_key=false): void
	{
		$api_key        = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : $api_key;
		$campaign_id    = isset($_POST['campaign_id']) ? sanitize_text_field($_POST['campaign_id']) : false;

		if( ! empty($api_key) && ! empty($campaign_id) ) {
			$client     = new ApiClient($api_key);
			$campaign   = $client->get_campaign($campaign_id);

			if( ! $client->has_error() ) {
				Settings::set_setting('campaign', $campaign);
				Settings::set_setting('api_key', $api_key);
				Settings::set_setting('auto_embed_pixel', 'on');

				echo $this->load_template('admin-setup-completed');

				return;
			}

		} else if( !empty($api_key) ) {
			$client     = new ApiClient($api_key);
			$campaigns  = $client->get_campaigns();
		}

		echo $this->load_template('admin-setup', [
			'api_key'       => $api_key ?? false,
			'client'        => $client ?? false,
			'campaigns'     => $campaigns ?? false
		]);
	}

	/**
	 * Template Loader
	 *
	 * @param string $template_name
	 * @param array $data
	 *
	 * @return string
	 */
	public function load_template(string $template_name, array $data = []): string
	{
		$path = PUBA_DIR.'/templates/'.$template_name.'.php';

		if( file_exists($path) ) {
			ob_start();

			if( is_array($data) && count($data) >= 1 ) {
				extract($data, EXTR_OVERWRITE);
			}

			require $path;
			$template = ob_get_contents();
			ob_end_clean();

			return $template;
		} else {
			return false;
		}
	}
}