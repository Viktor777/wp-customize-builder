<?php

namespace modules\builder\controls;

/**
 * Class Header
 *
 * @package modules\builder\controls
 */
class Header extends AbstractControl
{
	/**
	 * Type of control
	 * @var string
	 */
	public $type = 'header';

	/**
	 * Render the control's content
	 */
	public function render_content()
	{
		if ( !empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?= esc_html( $this->label ) ?></span>
		<?php endif;
		if ( !empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?= $this->description ?></span>
		<?php endif;
	}
}