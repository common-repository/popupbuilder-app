<?php

namespace BM\PopUpBuilder;

/* Security-Check */
if (!class_exists('WP')) {
	die();
}

class Settings
{
	private static $prefix = 'puba_';

	public function __construct()
	{

	}

	public static function get_setting($key, $default=false)
	{
		return get_option(self::$prefix.$key, $default);
	}

	public static function set_setting($key, $val):bool
	{
		return update_option(self::$prefix.$key, $val);
	}

	public static function delete_setting($key):bool
	{
		return delete_option(self::$prefix.$key);
	}

	public static function get_notifications($type=false)
	{
		$notifications = self::get_setting('notifications', false);

		if( (is_string($type) && strlen($type) >= 3) || is_array($type) ) {

			$return_array = [];

			if( is_array($notifications) ) {

				if( is_array($type) ) {
					$type = array_map('strtoupper', $type);
				}

				foreach ($notifications as $notification) {

					if( is_array($type) && in_array( strtoupper($notification->type), $type ) ) {
						$return_array[] = $notification;
					} else if( is_string($type) && strtoupper($notification->type) === strtoupper($type) ) {
						$return_array[] = $notification;
					}
				}

				if( count($return_array) >= 1 ) return $return_array;
			}

			return false;
		} else {
			return $notifications;
		}
	}
}