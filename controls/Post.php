<?php

namespace modules\builder\controls;

use modules\builder\helpers\Posts;

/**
 * Class Post
 *
 * @package modules\builder\controls
 */
class Post extends AbstractGroup
{
	/**
	 * Type of control
	 * @var string
	 */
	public $type = 'post';
	/**
	 * Post type
	 * @var string
	 */
	public $post_type = 'post';

	/**
	 * Constructor
	 *
	 * @uses AbstractGroup::__construct()
	 *
	 * @param \WP_Customize_Manager $manager
	 * @param string                $id
	 * @param array                 $args
	 */
	public function __construct( $manager, $id, array $args )
	{
		parent::__construct( $manager, $id, $args );

		if ( !empty( $args['post_type'] ) ) {
			$this->post_type = $args['post_type'];
		}
	}

	protected function _build_controls()
	{
		$this->_add_control( '_header', [
			'description' => $this->description,
			'label'       => $this->label
		], 'Header' );
		$this->manager->add_setting( "{$this->id}[post_id]", [
			'type'      => 'option',
			'transport' => 'postMessage'
		] );

		add_action( "customize_save_{$this->_panel}", function ( $setting ) {

			if ( $setting->id == "{$this->id}[post_id]" ) {
				$this->_save_post_id( $setting );
			}
		} );

		$control = new \WP_Customize_Control( $this->manager, "{$this->id}[post_id]", [
			'type'        => 'hidden',
			'section'     => $this->section,
		    'input_attrs' => [
			    'style'            => 'width: 100%;',
		        'data-post_type'   => isset( $this->post_type ) ? $this->post_type : 'post',
		        'data-placeholder' => __( '&mdash; Select &mdash;' )
		    ]
		] );
		$control->input_attrs['data-value'] = get_the_title( $control->value() );
		$this->manager->add_control( $control );
	}

	/**
	 * Enqueue control related scripts/styles
	 */
	public function enqueue()
	{
		parent::enqueue();

		wp_enqueue_style(
			'select2',
			get_template_directory_uri() . '/modules/builder/assets/vendor/select2/select2.css',
			[],
			'3.5.2'
		);
		wp_enqueue_script(
			'select2',
			get_template_directory_uri() . '/modules/builder/assets/vendor/select2/select2.min.js',
			[ 'jquery' ],
			'3.5.2',
			true
		);
		wp_enqueue_script(
			"customize-controls-{$this->type}",
			get_template_directory_uri() . "/modules/builder/assets/scripts/controls/customize-controls-{$this->type}.js",
			[ 'jquery', 'select2', 'customize-controls' ],
			null,
			true
		);
		wp_localize_script( "customize-controls-{$this->type}", 'customizeControlsBuilderPost', [
			'actions' => [
				'search' => \Builder::get_ajax_action( 'customize_control_post_search' )
			],
			'nonces'  => [
				'search' => wp_create_nonce()
			],
		] );
	}

	/**
	 * Save data in special option for better performance in query
	 *
	 * @param \WP_Customize_Setting $setting
	 */
	protected function _save_post_id( $setting )
	{
		Posts::update( $setting->id, $setting->post_value() );
	}
}