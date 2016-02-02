<?php

namespace modules\builder\widgets;

/**
 * Class Text
 *
 * @package modules\builder\widgets
 */
class Text extends AbstractWidget
{
	/**
	 * Get widget configurations
	 *
	 * @return array
	 */
	public static function get_args()
	{
		return [
			'title' => __( 'Text' )
		];
	}

	/**
	 * Add controls (fields) to widget
	 */
	public function build_controls()
	{
		$this->add_control( 'title', [
			'label' => __( 'Title' )
		] );
		$this->add_control( 'content', [
			'label' => __( 'Content' ),
			'type'  => 'textarea'
		] );
		$this->add_control( 'link_url', [
			'label' => __( 'URL' )
		] );
		$this->add_control( 'link_text', [
			'label' => __( 'Link Text' )
		] );
		$this->add_control( 'link_target', [
			'label' => __( 'Open link in a new window/tab' ),
			'type'  => 'checkbox',
			'value' => '1'
		] );
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
		if ( !empty( $data['title'] ) ) :
			echo apply_filters( 'the_title', $data['title'] );
		endif;

		if ( !empty( $data['content'] ) ) :
			echo apply_filters( 'the_excerpt', $data['content'] );
		endif;

		if ( !empty( $data['link_url'] ) && !empty( $data['link_text'] ) ) : ?>
			<a href="<?= esc_url( $data['link_url'] ) ?>"<?= !empty( $data['link_target'] ) ? ' target="_blank"' : '' ?>><?= apply_filters( 'the_title', $data['link_text'] ) ?></a>
		<?php endif;
	}
}