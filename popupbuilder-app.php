<?php
/**
 * Plugin Name:         PopUpBuilder.App
 * Description:         PopUpBuilder.App is a easy to use and simple plugin to create user interactions with notifications and push your conversionrate.
 * Author:              Bajorat Media
 * Author URI:          https://www.bajorat-media.com
 * Plugin URI:          https://popupbuilder.app
 * Version:             1.0.2
 * Requires at least:   5.2
 * Requires PHP:        7.3
 * Text Domain:         puba
 * Domain Path:         /languages
 **/

use BM\PopUpBuilder;

/* Security-Check */
if (!class_exists('WP')) {
	die();
}

/* Constants */
define('PUBA_FILE', __FILE__);
define('PUBA_DIR', dirname(__FILE__));
define('PUBA_BASE', plugin_basename(__FILE__));
define('PUBA_VERSION', '1.0.2');

/**
 * PopUpBuilderApp Main Class
 */
class PopUpBuilderApp {
	public function __construct()
	{
		add_action('init',                              [$this, 'load_textdomain']);

		/*
		 * General
		 * ************************
		 */
		new PopUpBuilder\RestApi();
		new PopUpBuilder\WooCommerce();

		if( is_admin() ) {
			/*
			 * Admin functions
			 * ************************
			 */

			new PopUpBuilder\Admin();

		} else {
			/*
			 * Frontend functions
			 * ************************
			 */

			new PopUpBuilder\Frontend();

		}
	}

	public function load_textdomain(): void
	{
		$path = dirname( plugin_basename(PUBA_FILE) ) . '/languages';
		load_plugin_textdomain( 'popupbuilder-app', false, $path );
	}
}

add_action('plugins_loaded', function(){
	new \PopUpBuilderApp();
});

/* Autoload Init */
spl_autoload_register(function($class) {

	// project-specific namespace prefix
	$prefix = 'BM\\PopUpBuilder\\';

	// base directory for the namespace prefix
	$base_dir = __DIR__ . '/src/';

	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = substr($class, $len);

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	// if the file exists, require it
	if (file_exists($file)) {
		require $file;
	}
});