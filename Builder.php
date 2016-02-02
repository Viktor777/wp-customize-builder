<?php

namespace modules\builder;

use WPKit\Exception\WpException;

/**
 * Class Builder
 *
 * @package modules\builder
 */
final class Builder
{
	/**
	 * Panel (builder) configurations
	 * @var array
	 */
	private $_defaults = [
		'priority'   => 110
	];
	/**
	 * Widgets (sections) which are included in panel (builder)
	 * @var array
	 */
	private $_widgets = [];
	/**
	 * Capability required for the panel (builder)
	 * @var string
	 */
	private static $_capability = 'edit_theme_options';
	/**
	 * Panel id
	 * @var string
	 */
	private $_panel;

	/**
	 * Constructor
	 *
	 * @param string $panel
	 * @param array  $settings
	 */
	public function __construct( $panel, array $settings )
	{
		global $wp_customize;

		$this->_panel = $panel;
		$settings['capability'] = static::$_capability;
		$this->_settings = wp_parse_args( $settings, $this->_defaults );
		$this->_assets_url = get_template_directory_uri() . '/modules/builder/assets';

		add_action( 'customize_register', function ( $wp_customize ) {
			$this->_register( $wp_customize );
		} );
		add_action( 'customize_controls_enqueue_scripts', function () {
			$this->_enqueue_styles();
			$this->_enqueue_scripts();
		} );
		add_action( "{$this->_panel}_render", function ( $args ) {
			$this->_render( $args );
		} );
		add_action( 'customize_preview_init', function () {
			$this->_enqueue_preview_styles();
			$this->_enqueue_preview_scripts();
		} );
		add_action( "wp_ajax_get_{$this->_panel}_widget_content", function () {
			$this->_action_get_widget_content();
		} );

		if ( isset( $wp_customize ) ) {
			add_action( 'wp_head', function () {
				$this->_set_color_scheme();
			}, 99 );
			add_action( 'wp_footer', function () {
				$this->_add_preview_helpers_blocks();
			}, 1 );
		}
	}

	/**
	 * Set widgets
	 *
	 * @param array $widgets
	 */
	public function set_widgets( array $widgets )
	{
		$this->_widgets = $widgets;
	}

	/**
	 * Register builder (panel etc.)
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	private function _register( $wp_customize )
	{
		$wp_customize->add_panel( $this->_panel, $this->_settings );

		foreach ( $this->_widgets as $number => $widget ) {
			$key = "{$this->_panel}[$number]";

			if ( !is_array( $widget ) ) {
				$this->_register_widget( $wp_customize, $key, $widget );
			} else {
				$this->_register_widget( $wp_customize, $key, 'WidgetSwitcher', [
					'available_widgets' => $widget
				] );
				$active_widget = $wp_customize->get_setting( "{$key}[active_widget]" )->value();

				foreach ( $widget as $_number => $_widget ) {
					$args = [];

					if ( $active_widget === $_number ) {
						$wp_customize->get_section( $key )->active_callback = function () {
							return false;
						};
					} else {
						$args['active_callback'] = function () {
							return false;
						};
					}
					$this->_register_widget( $wp_customize, "{$key}[$_number]", $_widget, $args )->add_control( 'remove', [
						'label'       => __( 'Remove' ),
					    'description' => __( 'Trash widget by moving it to inactive widgets list.' )
					], 'Button' );
				}
			}
		}
	}

	/**
	 * Register widget (section)
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 * @param string                $key
	 * @param string                $widget
	 * @param array                 $args
	 *
	 * @return widgets\AbstractWidget
	 */
	private function _register_widget( $wp_customize, $key, $widget, $args = [] )
	{
		$class = __NAMESPACE__ . "\\widgets\\$widget";
		$args = wp_parse_args( [
			'capability' => $this->_settings['capability'],
			'panel'      => $this->_panel
		], $args );
		/**
		 * @var $widget widgets\AbstractWidget
		 */
		$widget = new $class( $wp_customize, $key, $args );
		$wp_customize->add_section( $widget );
		$widget->build_controls();

		return $widget;
	}

	private function _enqueue_styles()
	{
		wp_enqueue_style(
			'customize-builder',
			"{$this->_assets_url}/styles/customize-builder.css"
		);
	}

	private function _enqueue_scripts()
	{
		wp_enqueue_script(
			'customize-builder',
			"{$this->_assets_url}/scripts/customize-builder.js",
			[ 'jquery', 'customize-controls' ],
			null,
			true
		);
		wp_localize_script(
			'customize-builder',
			'customizeBuilder',
			[
				'panel' => $this->_panel
			]
		);
	}

	private function _enqueue_preview_styles()
	{
		wp_enqueue_style(
			'customize-builder-preview',
			"{$this->_assets_url}/styles/customize-builder-preview.css"
		);
	}

	private function _enqueue_preview_scripts()
	{
		wp_enqueue_script(
			'customize-builder-preview',
			"{$this->_assets_url}/scripts/customize-builder-preview.js",
			[ 'jquery', 'customize-preview' ],
			null,
			true
		);
		wp_localize_script(
			'customize-builder-preview',
			'customizeBuilderPreview',
			[
				'action' => "get_{$this->_panel}_widget_content",
				'nonce'  => wp_create_nonce(),
			    'panel'  => $this->_panel
			]
		);
	}

	/**
	 * Display builder on frontend
	 *
	 * @param array $args
	 */
	private function _render( array $args )
	{
		global $wp_customize;

		$data = get_option( $this->_panel );
		$args = wp_parse_args( $args, [
			'before' => '',
			'after'  => ''
		] );

		if ( !empty( $data ) || isset( $wp_customize ) ) {
			/**
			 * Need to make it for calling widgets\AbstractWidget::render_widget()
			 */
			require_once ABSPATH . WPINC . '/class-wp-customize-section.php';
			do_action( "{$this->_panel}_before" );
			echo $args['before'];

			foreach ( $this->_widgets as $number => $widget ) {
				if ( !is_array( $widget ) ) {
					$this->_render_widget( !empty( $data[ $number ] ) ? $data[ $number ] : [], $number, $widget );
				} else {
					if ( !empty( $data[ $number ] ) ) {
						$_data = $data[ $number ];
						$active_widget = isset( $_data['active_widget'] ) ? $_data['active_widget'] : null;

						if ( $active_widget !== '' && $active_widget !== null && isset( $widget[ $active_widget ] ) ) {
							$_data['active_widget'] = [
								'number' => $_data['active_widget'],
								'widget' => $widget[ $active_widget ]
							];
						}
					} else {
						$_data = [];
					}
					$this->_render_widget( $_data, $number, 'WidgetSwitcher' );
				}
			}

			echo $args['after'];
			do_action( "{$this->_panel}_after" );
		}
	}

	/**
	 * Display widget (section)
	 *
	 * @param array      $data
	 * @param int        $number Number of widget
	 * @param string     $widget Widget name
	 */
	private function _render_widget( $data, $number, $widget )
	{
		/**
		 * @var $class widgets\AbstractWidget
		 */
		$class = __NAMESPACE__ . "\\widgets\\$widget";
		$id = "{$this->_panel}[$number]";

		if ( !empty( $data['active_widget']['widget'] ) ) {
			/**
			 * @var $_class widgets\AbstractWidget
			 */
			$_class = __NAMESPACE__ . '\\widgets\\' . $data['active_widget']['widget'];
			$args = $_class::get_args();
		} else {
			$args = $class::get_args();
		}
		$attrs = static::get_widget_attrs( $id, $args );

		echo apply_filters( "{$this->_panel}_before_widget", "<div $attrs>", $number, $attrs );
		$class::render_widget( $data, $id, $number );
		echo apply_filters( "{$this->_panel}_after_widget", '</div>', $number, $attrs );
	}

	private function _action_get_widget_content()
	{
		check_ajax_referer();

		if ( !empty( $_GET['_widget'] ) && !empty( $_GET['_id'] ) && !empty( $_GET['data'] ) ) {
			/**
			 * Need to make it for calling widgets\AbstractWidget::render_widget()
			 */
			require_once ABSPATH . WPINC . '/class-wp-customize-section.php';
			/**
			 * @var $class widgets\AbstractWidget
			 */
			$class = __NAMESPACE__ . '\\widgets\\' . sanitize_text_field( $_GET['_widget'] );

			if ( class_exists( $class ) ) {
				$id = sanitize_text_field( $_GET['_id'] );
				$number = absint( preg_replace( "/\\w+\\[(\\d+)\\]/i", '$1', $id ) );

				ob_start();
				$class::render_widget( stripslashes_deep( $_GET['data'] ), $id, $number );
				wp_send_json_success( ob_get_clean() );
			}
		}

		wp_send_json_error();
	}

	private function _set_color_scheme()
	{
		global $_wp_admin_css_colors;

		register_admin_color_schemes();

		$admin_color = get_user_option( 'admin_color' );

		if ( isset( $_wp_admin_css_colors[ $admin_color ] ) ) :
			$shadow = implode( ', ', static::hex2rgb( $_wp_admin_css_colors[ $admin_color ]->colors[1] ) );
			?>
			<style>
				#builder-widget-overlay-clicked {
					-webkit-box-shadow: 0 0 0 9999px rgba(<?= $shadow ?>, 0.5);
					box-shadow: 0 0 0 9999px rgba(<?= $shadow ?>, 0.5);
				}
				#builder-widget-overlay-hovered {
					background-color: rgba(<?= implode( ', ', static::hex2rgb( $_wp_admin_css_colors[ $admin_color ]->colors[0] ) ) ?>, 0.65);
					border: 1px solid <?= $_wp_admin_css_colors[ $admin_color ]->colors[0] ?>;
					text-shadow: 2px 2px <?= $_wp_admin_css_colors[ $admin_color ]->colors[0] ?>;
				}
			</style>
			<?php
		endif;
	}

	private function _add_preview_helpers_blocks()
	{
		echo '<div id="builder-widget-overlay-hovered"></div>';
		echo '<div id="builder-widget-overlay-clicked"></div>';
	}

	/**
	 * Returns capability required for the panel (builder)
	 *
	 * @return string
	 */
	public static function get_capability()
	{
		return static::$_capability;
	}

	/**
	 * Returns widget wrapper attributes
	 *
	 * @param string $id
	 * @param array  $args
	 *
	 * @return string
	 */
	public static function get_widget_attrs( $id, array $args )
	{
		global $wp_customize;

		$attrs = '';

		if ( isset( $wp_customize ) || defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$attrs .= 'data-widget="' . esc_attr( $id ) . '"';

			if ( isset( $args['title'] ) ) {
				$attrs .= ' title="' . esc_attr( $args['title'] ) . '"';
			}
		}

		return $attrs;
	}

	/**
	 * Converts hexadecimal color format to RGB
	 *
	 * @param $hex
	 *
	 * @return array
	 */
	public static function hex2rgb( $hex ) {
		$hex = str_replace( '#', '', $hex );

		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( $hex[0] . $hex[0] );
			$g = hexdec( $hex[1] . $hex[1] );
			$b = hexdec( $hex[2] . $hex[2] );
		} else {
			$r = hexdec( $hex[0] . $hex[1] );
			$g = hexdec( $hex[2] . $hex[3] );
			$b = hexdec( $hex[4] . $hex[5] );
		}

		return [ $r, $g, $b ];
	}
}