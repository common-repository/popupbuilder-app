<?php
/**
 * @var $client \BM\PopUpBuilder\ApiClient
 * @var $campaigns array
 * @var $api_key string
 */

$allowed_html = array(
	'a' => [
		'href' =>[],
		'title' => [],
        'target' => []
	],
	'br' => [],
);

$api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : $api_key;
?>
<div class="wrap puba-wrapper">
	<h1 class="puba-logo"><?php esc_html_e( 'PopUpBuilder.App', 'popupbuilder-app' ); ?></h1>

    <?php
    if( $client && $client->has_error() ) {
        echo $client->render_errors();
    }
    ?>

    <?php if( !empty($api_key) && ! $client->has_error() ): ?>

        <div class="api-key-box">
            <h2>2. <?php esc_html_e( 'Please select your campaign ...', 'popupbuilder-app' ); ?></h2>
            <p><?php esc_html_e( 'Please select the campaign you want to connect.', 'popupbuilder-app' ); ?></p>
            <form action="<?php echo esc_url(admin_url('options-general.php?page=popupbuilderapp')); ?>" method="post">
                <input type="hidden" name="setup" value="true" />
                <input name="api_key" type="hidden" value="<?php echo esc_attr($api_key); ?>" required />
			    <?php
			    if( is_array($campaigns) && count($campaigns) >= 1 ) {
				    echo '<select name="campaign_id">';
				    foreach ($campaigns as $campaign) {
					    $selected = strstr(strtolower(home_url('/')), strtolower($campaign->domain)) ? 'selected="selected"' : '';
					    echo '<option value="'.esc_attr($campaign->id).'" '.$selected.'>'.esc_html($campaign->name).' ('.esc_html($campaign->domain).')</option>';
				    }
				    echo '</select>';
				    echo '<input type="submit" value="'.esc_attr__( 'Select Campaign', 'popupbuilder-app' ).'" />';
			    } else {
				    echo '<p><strong>'.esc_html__('Sorry, we can not find any campaigns in your account. Please create one before we can continue here.', 'popupbuilder-app').'</strong></p>';
			    }
			    ?>
            </form>
        </div>

    <?php else: ?>

        <div class="api-key-box">
            <h2>1. <?php esc_html_e( 'Please enter your API Key ...', 'popupbuilder-app' ); ?></h2>
            <p><?php echo wp_kses(__( 'Please enter your <a href="https://my.popupbuilder.app/account-api" target="_blank">PopUpBuilder.App API-Key</a> to connect the Plugin.<br/>Don´t have an API-Key? <a href="https://my.popupbuilder.app/register" target="_blank">Register for an account, it´s free!</a>', 'popupbuilder-app' ), $allowed_html); ?></p>
            <form action="<?php echo admin_url('options-general.php?page=popupbuilderapp'); ?>" method="post">
                <input type="hidden" name="setup" value="true" />
                <input name="api_key" type="text" value="<?php echo esc_attr($api_key); ?>" placeholder="<?php esc_attr_e( 'Please enter your API Key ...', 'popupbuilder-app' ); ?>" required />
                <input type="submit" value="<?php esc_attr_e( 'Sign In', 'popupbuilder-app' ); ?>" />
            </form>
        </div>

        <div class="discover">
            <a href="https://popupbuilder.app/" target="_blank" class="button purple"><?php esc_html_e( 'Discover PopUpBuilder.App', 'popupbuilder-app' ); ?></a>
        </div>

    <?php endif; ?>

</div>