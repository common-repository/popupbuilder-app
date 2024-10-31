<?php

namespace BM\PopUpBuilder;

/* Security-Check */
if (!class_exists('WP')) {
	die();
}

class Frontend
{
	public function __construct()
	{
		add_action( 'wp_enqueue_scripts',                    [$this, 'add_pixel'] );
	}

	public function add_pixel()
	{
		$api_key        = Settings::get_setting('api_key');
		$campaign       = Settings::get_setting('campaign');
		$embed_pixel    = Settings::get_setting('auto_embed_pixel');

		if( ! empty($api_key) && ! empty($campaign) && isset($campaign->pixel_key) && $embed_pixel === 'on' ) {
			wp_enqueue_script('puba', 'https://my.popupbuilder.app/pixel/'.$campaign->pixel_key, null, PUBA_VERSION, true);
		}
	}
}