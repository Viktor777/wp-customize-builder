<?php

namespace modules\builder\widgets;

/**
 * Class Video
 *
 * @package modules\builder\widgets
 */
class Video extends AbstractWidget
{
	/**
	 * Get widget configurations
	 *
	 * @return array
	 */
	public static function get_args()
	{
		return [
			'title' => __( 'Video' )
		];
	}

	/**
	 * Add controls (fields) to widget
	 */
	public function build_controls()
	{
		$this->add_control( 'video', [
			'label' => __( 'Video' )
		] );
		$this->add_control( 'thumbnail', [
			'label' => __( 'Featured Image' )
		], 'WP_Customize_Image_Control' );
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
		if ( !empty( $data['video'] ) ) :
			echo wp_oembed_get( $data['video'] );
		endif;

		if ( !empty( $data['thumbnail'] ) && ( $image_id = absint( attachment_url_to_postid( $data['thumbnail'] ) ) ) && wp_attachment_is_image( $image_id ) ) :
			echo wp_get_attachment_image( $image_id, 'medium' );
		endif;
	}
}