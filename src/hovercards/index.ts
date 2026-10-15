import { Hovercards } from '@gravatar-com/hovercards';
import '@gravatar-com/hovercards/dist/style.css';

// The WooCommerce Customer Account block shows the logged-in user's avatar as a link to My Account, so a hovercard there gets in the way.
const ignoreSelector =
	'#wpadminbar img, img.gravatar-hovercard__avatar, .user-profile-picture img, .wp-block-woocommerce-customer-account img';

document.addEventListener( 'DOMContentLoaded', () => {
	const hovercards = new Hovercards( {
		i18n: typeof gravatarEnhancedHovercardsI18n !== 'undefined' ? gravatarEnhancedHovercardsI18n : {},
	} );

	hovercards.attach( document.body, { ignoreSelector } );
} );
