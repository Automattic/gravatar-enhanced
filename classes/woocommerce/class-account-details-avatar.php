<?php

namespace Automattic\Gravatar\GravatarEnhanced\Woocommerce;

class AccountDetailsAvatar {
	public function init() {
		add_action('woocommerce_before_edit_account_form', [ $this, 'display_gravatar' ] );
	}

	function display_gravatar() {
		$user_email = wp_get_current_user()->user_email;

		?>
		<div class="account-gravatar">
			<?php echo get_avatar( $user_email, 100, '', 'User Avatar', array( 'class' => 'woocommerce-account-gravatar' ) );
			?>
		</div>
		<?php
	}
}
