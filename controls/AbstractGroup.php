<?php

namespace modules\builder\controls;

/**
 * Class AbstractGroup
 *
 * @package modules\builder\controls
 */
abstract class AbstractGroup extends AbstractControl
{
	/**
	 * Type of control
	 * @var string
	 */
	public $type = 'group';

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
		$this->_build_controls();
	}

	/**
	 * Method for adding controls (use $this->_add_control())
	 */
	abstract protected function _build_controls();

	/**
	 * Does not render anything in group
	 */
	final public function render()
	{

	}

	/**
	 * @param string $id
	 * @param array  $args
	 * @param string $control
	 */
	final protected function _add_control( $id, array $args, $control = 'WP_Customize_Control' )
	{
		$this->manager->add_setting( "{$this->id}[$id]", [
			'type'      => 'option',
			'transport' => 'postMessage'
		] );
		$args['section'] = $this->section;
		$class = class_exists( "\\$control" ) ? "\\$control" : __NAMESPACE__ . "\\$control";
		$this->manager->add_control( new $class( $this->manager, "{$this->id}[$id]", $args ) );
	}
}