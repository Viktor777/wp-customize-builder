<?php

namespace modules\builder\helpers;

/**
 * Class Posts
 *
 * @package modules\builder\helpers
 */
class Posts
{
	/**
	 * @var array|null
	 */
	protected static $_posts = null;
	/**
	 * @var array
	 */
	protected static $_post_types = [
		'post' => 4
	];
	/**
	 * @var string
	 */
	protected static $_key = 'builder_posts';
	/**
	 * @var array
	 */
	protected static $_query_args = [];

	/**
	 * @param int    $post_id
	 * @param string $post_type
	 * @param string $key
	 *
	 * @return \WP_Post
	 */
	public static function get( $post_id = 0, $post_type = 'post', $key = '' )
	{
		$args = [
			'ignore_sticky_posts' => true
		];

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$args['post_type'] = array_keys( static::$_post_types );

			if ( $post_id ) {
				$args['p'] = $post_id;
				$post = current( ( new \WP_Query( $args ) )->get_posts() );
			} else {
				Posts::update( $key, $post_id );
				$post = null;
			}
		} else {
			if ( is_null( static::$_posts ) ) {
				$args['post_type'] = array_keys( static::$_post_types );
				$args['posts_per_page'] = -1;
				$ids = array_values( static::get_ids() );

				if ( !empty( $ids ) ) {
					$args['post__in'] = $ids;
					$the_query = new \WP_Query( $args );

					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$_post_type = get_post_type();

						if ( !isset( static::$_posts[ $_post_type ] ) ) {
							static::$_posts[ $_post_type ] = [];
						}
						static::$_posts[ $_post_type ][ get_the_ID() ] = get_post();
					}

					wp_reset_postdata();

					$args['post__not_in'] = $ids;
					unset( $args['post__in'] );
				}

				foreach ( static::$_post_types as $_post_type => $_posts_per_page ) {
					if ( !isset( static::$_posts[ $_post_type ] ) ) {
						static::$_posts[ $_post_type ] = [];
					}
					$_found_posts = count( static::$_posts[ $_post_type ] );

					if ( $_found_posts < $_posts_per_page ) {
						$args['post_type'] = $_post_type;
						$args['posts_per_page'] = $_posts_per_page - $_found_posts;
						$the_query = new \WP_Query( $args );
						static::$_query_args[ $_post_type ] = [
							'offset' => $the_query->post_count
						];
						static::$_posts[ $_post_type ]['recent'] = $the_query->get_posts();
					}
				}
			}

			if ( $post_id && isset( static::$_posts[ $post_type ][ $post_id ] ) ) {
				$post = static::$_posts[ $post_type ][ $post_id ];

				unset( static::$_posts[ $post_type ][ $post_id ] );
			} else {
				$post_id = key( static::$_posts[ $post_type ]['recent'] );
				$post = isset( static::$_posts[ $post_type ]['recent'][ $post_id ] ) ? static::$_posts[ $post_type ]['recent'][ $post_id ] : null;

				unset( static::$_posts[ $post_type ]['recent'][ $post_id ] );
			}
		}

		return $post;
	}

	/**
	 * @param $key
	 * @param $post_id
	 */
	public static function update( $key, $post_id )
	{
		$post_ids = static::get_ids();
		$post_ids[ $key ] = absint( $post_id );

		update_option( static::$_key, $post_ids, false );
	}

	/**
	 * @return array|mixed
	 */
	public static function get_ids()
	{
		return ( $ids = get_option( static::$_key ) ) ? array_filter( $ids ) : [];
	}

	/**
	 * @return array
	 */
	public static function get_query_args()
	{
		return static::$_query_args;
	}
}