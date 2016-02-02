<?php

namespace modules\builder;

use WPKit\Module\AbstractInitialization;

/**
 * Class Initialization
 *
 * @package modules\builder
 */
class Initialization extends AbstractInitialization
{
	/**
	 * @var Builder
	 */
	protected $_builder;

	/**
	 * Example of builder initialization
	 */
	public function register_builder()
	{
		$this->_builder = new Builder( Functions::get_key(), [
			'title'           => __( 'Builder' ),
		    'active_callback' => function () {
			    return is_home() || is_front_page();
		    }
		] );
		$this->_builder->set_widgets( [
			'Post',
			[ 'Video', 'Text' ],
			'Post',
			'Video',
			'Post',
			'Post',
			[ 'Video', 'Text' ]
		] );
	}
}