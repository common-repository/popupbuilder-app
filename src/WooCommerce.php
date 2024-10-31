<?php

namespace BM\PopUpBuilder;

/* Security-Check */
if (!class_exists('WP')) {
	die();
}

class WooCommerce
{
	public function __construct()
	{
		add_action('woocommerce_new_order',                         [$this, 'woocommerce_new_order'], 10, 1);
	}

	public function woocommerce_new_order( $order_id )
	{
		try {
			$settings   = Settings::get_setting('woocommerce_orders', []);

			if( ! is_array($settings) || count($settings) <= 0 ) return;

			$order = wc_get_order($order_id);

			if( $order ) {
				if( isset(WC()->countries->countries[ $order->get_billing_country() ]) ) {
					$country = WC()->countries->countries[ $order->get_billing_country() ];
				} else {
					$country = $order->get_billing_country();
				}

				$items      = [];

				foreach ($order->get_items() as $item) {
					$items[]    = $item->get_name();
				}

				foreach($settings as $hook_id) {
					$send_data = wp_remote_post( 'https://my.popupbuilder.app/pixel-webhook/'.$hook_id, [
						'method'      => 'POST',
						'body'        => array(
							'first_name'        => $order->get_billing_first_name(),
							'last_name'         => $order->get_billing_last_name(),
							'city'              => $order->get_billing_city(),
							'zip'               => $order->get_billing_postcode(),
							'country'           => $country,
							'order_value'       => $order->get_total(),
							'products_bought'   => implode(', ', $items),

						)
					]);

					if ( is_wp_error( $send_data ) ) {
						// Do we want to catch this?
					}
				}
			}
		} catch (\Exception $e) {

		}
	}
}