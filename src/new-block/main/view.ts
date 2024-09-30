/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { fetchProfile } from '../utils';

document.addEventListener( 'DOMContentLoaded', () => {
	const gravatarBlocks = document.querySelectorAll( '.gravatar-block' );

	gravatarBlocks.forEach( ( block ) => ( block.innerHTML = __( 'Loading…', 'gravatar-enhanced' ) ) );
} );
