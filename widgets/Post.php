<?php

namespace modules\builder\widgets;

use modules\builder\helpers\Posts;

/**
 * Class Post
 *
 * @package modules\builder\widgets
 */
class Post extends AbstractWidget
{
	/**
	 * Post type
	 * @var string
	 */
	protected static $_post_type = 'post';

	/**
	 * Get widget configurations
	 *
	 * @return array
	 */
	public static function get_args()
	{
		return [
			'title' => __( 'Post' )
		];
	}

	/**
	 * Add controls (fields) to widget
	 */
	public function build_controls()
	{
		$this->add_control( 'post', [
			'label'     => __( 'Post' ),
		    'post_type' => static::$_post_type
		], 'Post' );
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
		global $post;

		$post_id = !empty( $data['post']['post_id'] ) ? absint( $data['post']['post_id'] ) : 0;
		$post = Posts::get( $post_id, static::$_post_type, "{$id}[post][post_id]" );

		if ( !empty( $post ) ) :
			setup_postdata( $post );
			the_title();
			the_excerpt();
			wp_reset_postdata();
		endif;
	}
}