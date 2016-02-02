<?php

namespace modules\builder\controls;

/**
 * Class AbstractControl
 *
 * @package modules\builder\controls
 */
abstract class AbstractControl extends \WP_Customize_Control
{
	/**
	 * Control type
	 * @var string
	 */
	public $type = 'control';
	/**
	 * Control panel
	 * @var string
	 */
	protected $_panel = '';

	/**
	 * Constructor
	 *
	 * @uses \WP_Customize_Control::__construct()
	 *
	 * @param \WP_Customize_Manager $manager
	 * @param string                $id
	 * @param array                 $args
	 */
	public function __construct( $manager, $id, array $args )
	{
		parent::__construct( $manager, $id, $args );
		$this->_panel = $this->manager->get_section( $this->section )->panel;
		$this->type = "{$this->_panel}_{$this->type}";
	}
}