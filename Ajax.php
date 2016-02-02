<?php

namespace modules\builder;

use modules\builder\helpers\Posts;
use WPKit\Helpers\String;
use WPKit\Module\AbstractAjax;

/**
 * Class Ajax
 *
 * @package modules\builder
 */
class Ajax extends AbstractAjax
{
	/**
	 * Minimum number of characters to start search
	 */
	const MIN_CHARACTERS_NUMBER = 2;

	/**
	 * AJAX action for searching posts by category and title/content
	 */
	public function action_customize_control_post_search()
	{
		check_ajax_referer();

		if ( current_user_can( Builder::get_capability() ) ) {
			wp_send_json_success( wp_list_pluck( ( new \WP_Query( [
				'post_type'              => !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post',
				'cat'                    => !empty( $_GET['category'] ) ? absint( $_GET['category'] ) : 0,
				'paged'                  => !empty( $_GET['page'] ) ? absint( $_GET['page'] ) : 1,
				's'                      => !empty( $_GET['s'] ) && String::length( $s = sanitize_text_field( $_GET['s'] ) ) > static::MIN_CHARACTERS_NUMBER ? $s : '',
				'update_post_meta_cache' => false,
			    'post__not_in'           => array_values( Posts::get_ids() )
			] ) )->get_posts(), 'post_title', 'ID' ) );
		}

		wp_send_json_error( __( 'You are not allowed to do it.' ) );
	}
}