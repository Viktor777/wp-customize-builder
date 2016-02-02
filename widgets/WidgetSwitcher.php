<?php

namespace modules\builder\widgets;

/**
 * Class WidgetSwitcher
 *
 * @package modules\builder\widgets
 */
class WidgetSwitcher extends AbstractWidget
{
	/**
	 * Section type
	 * @var string
	 */
	public $type = 'widget_switcher';
	/**
	 * Collection of available widgets
	 * @var array
	 */
	protected $_available_widgets = [];

	/**
	 * Constructor
	 *
	 * @uses AbstractWidget::__construct()
	 *
	 * @param \WP_Customize_Manager $manager
	 * @param string                $id
	 * @param array                 $args
	 */
	public function __construct( $manager, $id, array $args )
	{
		parent::__construct( $manager, $id, $args );

		if ( !empty( $args['available_widgets'] ) ) {
			$this->_available_widgets = $args['available_widgets'];
		}

		add_action( 'customize_controls_print_footer_scripts', function () {
			$this->_render_widgets_list();
		} );
	}

	/**
	 * Method for adding controls (use $this->_add_control())
	 */
	public function build_controls()
	{
		$this->add_control( 'active_widget', [
			'type' => 'hidden'
		] );
	}

	/**
	 * Render the section, and the controls that have been added to it
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function render()
	{
		?>
		<li id="accordion-section-<?= esc_attr( $this->id ) ?>" class="accordion-section control-section control-section-<?= esc_attr( $this->type ) ?>">
			<button type="button" data-widget-id="<?= esc_attr( str_replace( '[', '-', str_replace( ']', '', $this->id ) ) ) ?>" class="button-secondary add-new-<?= esc_attr( $this->panel ) ?>-widget" aria-expanded="false">
				<?= static::get_args()['title'] ?>
			</button>
			<ul class="accordion-section-content clear"></ul>
		</li>
		<?php
	}

	/**
	 * Render available widgets list for current section
	 */
	protected function _render_widgets_list()
	{
		?>
		<ul id="available-<?= esc_attr( str_replace( '[', '-', str_replace( ']', '', $this->id ) ) ) ?>-widgets" class="available-<?= esc_attr( $this->panel ) ?>-widgets">
			<?php foreach ( $this->_available_widgets as $number => $widget ) :
				/**
				 * @var $class AbstractWidget
				 */
				$class = __NAMESPACE__ . "\\$widget";
				$args = $class::get_args(); ?>
				<li class="available-<?= esc_attr( $this->panel ) ?>-widget-<?= esc_attr( sanitize_key( $widget ) ) ?>">
					<button type="button" data-number="<?= esc_attr( $number ) ?>"><?= !empty( $args['title'] ) ? $args['title'] : $widget ?></button>
				</li>
			<?php endforeach ?>
		</ul>
		<?php
	}

	/**
	 * Returns config array with widget's info
	 *
	 * @return array
	 */
	public static function get_args()
	{
		return [
			'title' => __( 'Add a Widget' )
		];
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
		if ( isset( $data['active_widget']['number'] ) && !empty( $data['active_widget']['widget'] ) && isset( $data[ $data['active_widget']['number'] ] ) ) {
			/**
			 * @var $class AbstractWidget
			 */
			$class = __NAMESPACE__ . '\\' . $data['active_widget']['widget'];
			$id .= "[{$data['active_widget']['number']}]";
			$class::render_widget( $data[ $data['active_widget']['number'] ], $id, $number );
		}
	}
}