/*
 * Internal dependencies
 */
import { attachInlineHovercard } from '../hovercards/utils';

/**
 * Process all Gravatar Hovercard blocks on the page.
 * This will fetch the Gravatar hovercard data and render the hovercard for each of them.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var elements = document.querySelectorAll( '.wp-block-gravatar-block .gravatar-hovercard-container' );

	elements.forEach( function ( element ) {
		// Get email from `data-email` attribute and hash
		var hash = element.getAttribute( 'data-hash' );
		attachInlineHovercard( element, hash );
	} );
} );
