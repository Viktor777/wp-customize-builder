<?php

namespace modules\builder\widgets;

use WPKit\Exception\WpException;

/**
 * Class AbstractWidget
 *
 * @package modules\builder\widgets
 */
abstract class AbstractWidget extends \WP_Customize_Section
{
	/**
	 * Section type
	 * @var string
	 */
	public $type = 'widget';

	/**
	 * Constructor
	 *
	 * @uses \WP_Customize_Section::__construct()
	 *
	 * @param \WP_Customize_Manager $manager
	 * @param string                $id
	 * @param array                 $args
	 */
	public function __construct( $manager, $id, array $args )
	{
		$this->type = "{$args['panel']}_{$this->type}";
		parent::__construct( $manager, $id, wp_parse_args( $args, static::get_args() ) );
	}

	/**
	 * Gather the parameters passed to client JavaScript via JSON
	 *
	 * @return array
	 */
	public function json()
	{
		$exported = parent::json();
		$exported['widgetType'] = ( new \ReflectionClass( $this ) )->getShortName();

		return $exported;
	}

	/**
	 * Method for adding controls (use $this->_add_control())
	 */
	abstract public function build_controls();

	/**
	 * Add control (field) to widget
	 *
	 * @param        $id
	 * @param array  $args
	 * @param string $control
	 *
	 * @throws WpException
	 */
	final public function add_control( $id, array $args, $control = 'WP_Customize_Control' )
	{
		$this->manager->add_setting( "{$this->id}[$id]", [
			'type'      => 'option',
			'transport' => 'postMessage'
		] );
		$args['section'] = $this->id;
		$class = class_exists( "\\$control" ) ? "\\$control" : str_replace( 'widgets', "controls\\$control", __NAMESPACE__ );
		$this->manager->add_control( new $class( $this->manager, "{$this->id}[$id]", $args ) );
	}

	/**
	 * Returns config array with widget's info
	 *
	 * @return array
	 */
	public static function get_args()
	{
		return [];
	}

	/**
	 * Display widget content on frontend
	 *
	 * @param array  $data
	 * @param string $id
	 * @param int    $number
	 */
	public static function render_widget( array $data, $id, $number )
	{

	}
}