<?php

namespace modules\builder;

use WPKit\Module\AbstractFunctions;

/**
 * Class Functions
 *
 * @package modules\builder
 */
class Functions extends AbstractFunctions
{
	/**
	 * @var string
	 */
	protected static $_key = 'builder';

	/**
	 * @return string
	 */
	public static function get_key()
	{
		return static::$_key;
	}

	/**
	 * Returns builder data
	 *
	 * @return array
	 */
	public static function get_data()
	{
		return get_option( static::$_key );
	}

	/**
	 * Displays builder
	 *
	 * @param array $args
	 */
	public static function render( array $args )
	{
		do_action( static::$_key . '_render', $args );
	}
}