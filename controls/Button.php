<?php

namespace modules\builder\controls;

/**
 * Class Button
 *
 * @package modules\builder\controls
 */
class Button extends AbstractControl
{
	/**
	 * Type of control
	 * @var string
	 */
	public $type = 'button';

	/**
	 * Render the control's content
	 */
	public function render_content()
	{
		if ( !empty( $this->label ) ) : ?>
			<button class="button" title="<?= esc_attr( !empty( $this->description ) ? $this->description : $this->label ) ?>"><?= esc_html( $this->label ) ?></button>
		<?php endif;
	}
}