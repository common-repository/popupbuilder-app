<?php

namespace BM\PopUpBuilder;

/* Security-Check */
if (!class_exists('WP')) {
	die();
}

class ApiClient
{
	private $apiKey;
	private $baseURL            = 'https://my.popupbuilder.app/api';
	private $errors             = [];

	public function __construct($apiKey='')
	{
		$this->apiKey = $apiKey;
	}

	private function add_error($code, $message): void
	{
		$this->errors[] = [
			'code'      => $code,
			'message'   => $message
		];
	}

	public function is_good(): bool
	{
		return ! $this->has_error();
	}

	public function has_error(): bool
	{
		if( is_array($this->errors) && count($this->errors) >= 1 ) return true;
		return false;
	}

	public function get_errors(): array
	{
		return $this->errors;
	}

	public function render_errors(): string
	{
		if( ! $this->has_error() ) return '';

		$error_box = '<div class="puba-notice error">';
		$error_box .= '<p><strong>'.esc_html__('One or more errors occurred during the operation:', 'popupbuilder-app').'</strong></p>';
		$error_box .= '<ul>';
		foreach ($this->get_errors() as $error) {
			$error_box .= '<li><strong>'.esc_html($error['code']).'</strong> | '.esc_html($error['message']).'</li>';
		}
		$error_box .= '</ul></div>';

		return $error_box;
	}

	private function get_endpoint($endpoint, $data=[])
	{
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $this->apiKey,
			),
		);

		$api_result = wp_remote_get( $this->baseURL.'/'.$endpoint, $args );

		$response_code = wp_remote_retrieve_response_code( $api_result );
		$response_body = wp_remote_retrieve_body( $api_result );

		if( $response_code !== 200 ) {

		}

		return json_decode($response_body);
	}

	private function handle_api_error_response($errors): void
	{
		foreach ($errors as $error) {
			if( isset($error->status) && isset($error->title) ) {
				$this->add_error($error->status, $error->title);
			}
		}
	}

	public function get_campaigns($args=[])
	{
		$args = [
			'page'              => 1,
			'results_per_page'  => 500
		];

		$campaigns = $this->get_endpoint('campaigns', $args);

		if( isset($campaigns->errors) && count($campaigns->errors) >= 1 ) {
			$this->handle_api_error_response($campaigns->errors);
			return false;
		}

		return $campaigns->data;
	}

	public function get_campaign($id)
	{
		$campaign = $this->get_endpoint('campaigns/'.$id);

		if( isset($campaign->errors) && count($campaign->errors) >= 1 ) {
			$this->handle_api_error_response($campaign->errors);
			return false;
		}

		return $campaign->data;
	}

	public function get_notifications($args=[])
	{
		$args = [
			'page'              => 1,
			'results_per_page'  => 500
		];

		$notifications = $this->get_endpoint('notifications', $args);

		if( isset($notifications->errors) && count($notifications->errors) >= 1 ) {
			$this->handle_api_error_response($notifications->errors);
			return false;
		}

		return $notifications->data;
	}
}