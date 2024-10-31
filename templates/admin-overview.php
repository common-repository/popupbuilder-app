<?php
use BM\PopUpBuilder;

/**
 * @var $client \BM\PopUpBuilder\ApiClient
 * @var $campaigns array
 * @var $notifications array
 * @var $api_key string
 */

$api_key    = PopUpBuilder\Settings::get_setting('api_key');
$campaign   = PopUpBuilder\Settings::get_setting('campaign');
$auto_embed_pixel = PopUpBuilder\Settings::get_setting('auto_embed_pixel', 'off');
?>
<div class="puba-wrapper">
    <div class="puba-header clearfix">
	    <h1 class="puba-logo left"><?php esc_html_e( 'PopUpBuilder.App', 'popupbuilder-app' ); ?></h1>
        <div class="puba-status-box">
            <span class="label"><?php esc_html_e( 'Campaign', 'popupbuilder-app' ); ?>:</span> <span class="val"><?php echo esc_html($campaign->name); ?></span><br/>
            <span class="label"><?php esc_html_e( 'Domain', 'popupbuilder-app' ); ?>:</span> <span class="val"><?php echo esc_html($campaign->domain); ?></span><br/>
            <span class="label"><?php esc_html_e( 'API-Key', 'popupbuilder-app' ); ?>:</span> <span class="val">XXX<?php echo esc_html(substr($api_key, -4)); ?></span><br/>
            <div id="status-blob" class="blob black" title="<?php esc_attr_e( 'Connecting...', 'popupbuilder-app' ); ?>"></div>
        </div>
    </div>

	<?php
	if( $client && $client->has_error() ) {
		echo $client->render_errors();
	}
	?>

    <div class="info-box">
        <h2><?php esc_html_e( 'You are ready to go!', 'popupbuilder-app' ); ?></h2>
        <p><?php esc_html_e( 'You have sucessfully connected your account and pixel. This plugin will automatically add the pixel to your website.', 'popupbuilder-app' ); ?></p>
        <p><?php esc_html_e( 'If you have any problems with the delivery of your popups or notification widgets: Make sure that you have connected the correct campaign / domain and that your campaign and widgets are enabled. Check the status indicator (top right) for problems.', 'popupbuilder-app' ); ?></p>
        <a href="https://my.popupbuilder.app/campaign/<?php echo esc_attr($campaign->id); ?>" target="_blank" class="button purple"><?php esc_html_e( 'Go to PopUpBuilder.App', 'popupbuilder-app' ); ?></a> &nbsp; <a href="#integrations" class="button black"><?php esc_html_e( 'Configure WordPress Integration', 'popupbuilder-app' ); ?></a>
    </div>

    <?php if( is_array($notifications) && count($notifications) >= 1 ): ?>
    <h2><?php esc_html_e( 'Your notification widgets', 'popupbuilder-app' ); ?></h2>
    <div class="notifications-wrapper">
        <div class="row">
            <?php
            foreach ( $notifications as $notification ) {
                if( isset($campaign->id) && $campaign->id !== $notification->campaign_id ) continue;
                ?>
                <div class="col-lg-3 col-md-4 col-6">
                    <a href="https://my.popupbuilder.app/notification/<?php echo esc_attr($notification->id); ?>" target="_blank" class="notification-box">
                        <p><?php echo esc_html($notification->name); ?><br>
                        <?php
                        echo $notification->is_enabled ? '<span class="label enabled">'.esc_html__('Enabled', 'popupbuilder-app').'</span>' : '<span class="label disabled">'.esc_html__('Disabled', 'popupbuilder-app').'</span>';
                        ?></p>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <h2 id="integrations"><?php esc_html_e( 'Integration settings', 'popupbuilder-app' ); ?></h2>

    <div class="integrations-wrapper">
        <h2 class="with-checkbox"><label class="toggler-wrapper modern">
                <input type="checkbox" id="auto-embed-pixel" name="auto_embed_pixel" value="yes" <?php echo ($auto_embed_pixel=='on') ? 'checked="checked"' : '' ?> />
                <div class="toggler-slider">
                    <div class="toggler-knob"></div>
                </div>
            </label>  <?php esc_html_e( 'Automatically integrate PopUpBuilder.App pixel code', 'popupbuilder-app' ); ?></h2>

    </div>

    <div class="integrations-wrapper">
        <h2><?php esc_html_e( 'Configure Data Integrations between WordPress and PopUpBuilder.App', 'popupbuilder-app' ); ?></h2>
        <form action="#" method="post" class="ajax-form">
            <h3 class="headline-with-icon woocommerce"><?php esc_html_e( 'WooCommerce Orders', 'popupbuilder-app' ); ?></h3>
            <div class="integration-item">
                <div class="desc">
                    <p><?php esc_html_e('Notifications you activate here will receive WooCommerce orders, you can use the following data within you notification widgets:', 'popupbuilder-app'); ?></p>
                    <p><code title="<?php esc_attr_e( 'Billing first name', 'popupbuilder-app' ); ?>">{first_name}</code>, <code title="<?php esc_attr_e( 'Billing last name', 'popupbuilder-app' ); ?>">{last_name}</code>, <code title="<?php esc_attr_e( 'Billing city', 'popupbuilder-app' ); ?>">{city}</code>, <code title="<?php esc_attr_e( 'Billing postcode', 'popupbuilder-app' ); ?>">{zip}</code>, <code title="<?php esc_attr_e( 'Billing country', 'popupbuilder-app' ); ?>">{country}</code>, <code title="<?php esc_attr_e( 'Total order value', 'popupbuilder-app' ); ?>">{order_value}</code>, <code title="<?php esc_attr_e( 'Ordered products, comma separated product names', 'popupbuilder-app' ); ?>">{products_bought}</code></p>
                </div>
                <?php
                $wc_notifications = PopUpBuilder\Settings::get_notifications(['CONVERSIONS', 'CONVERSIONS_COUNTER']);

                if( is_array($wc_notifications) && count($wc_notifications) ) {
                    foreach ($wc_notifications as $checkbox) {
                        $setting    = 'woocommerce_orders';
                        $settings   = PopUpBuilder\Settings::get_setting($setting, []);
                        $checked    = in_array($checkbox->notification_key, $settings);
                        $class      = $checkbox->is_enabled ? 'enabled' : 'disabled';
                        $title      = $checkbox->is_enabled ? '' : __('This notification is disabled in our backend, you can send data, but the notification will not shown on your site.', 'popupbuilder-app');
                        echo '<label class="'.esc_attr($class).'" title="'.esc_attr($title).'"><input type="checkbox" name="woocommerce_orders[]" value="'.esc_attr($checkbox->notification_key).'" '.($checked ? 'checked="checked"' : '').'> '.esc_html($checkbox->name).'</label>';
                    }
                } else {
                    echo '<p class="empty-category">'.esc_html__( 'Create your first Conversion-PopUp before you can associate WooCommerce data..', 'popupbuilder-app' ).'</p>';
                }
                ?>
            </div>
        </form>
    </div>

    <p style="text-align: center;"><a href="<?php echo esc_url( admin_url('/') ); ?>" class="reconnect"><?php esc_html_e( 'Reconnect: Delete API key and settings', 'popupbuilder-app' ); ?></a></p>
</div>