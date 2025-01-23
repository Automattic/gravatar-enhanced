import type { MainEditAttrs } from './shared-types';
import { getDefaultTemplate } from './view-templates';
import { fetchProfile } from './utils';
import { __ } from '@wordpress/i18n';

import './shared.scss';
import './view.scss';

interface Attrs {
	hashedEmail: string;
	deletedElements: MainEditAttrs[ 'deletedElements' ];
}

document.addEventListener( 'DOMContentLoaded', () => {
	const gravatarBlocks = document.querySelectorAll< HTMLDivElement >( '.gravatar-block' );

	gravatarBlocks.forEach( async ( block ) => {
		block.innerHTML = __( 'Loading…', 'gravatar-enhanced' );

		if ( ! block.dataset.attrs ) {
			block.innerHTML = __( 'Oops! Something went wrong.', 'gravatar-enhanced' );
			return;
		}

		const { hashedEmail = '', deletedElements = {} } = JSON.parse( block.dataset.attrs ) as Attrs;

		const { error, data } = await fetchProfile( hashedEmail );

		if ( error ) {
			block.innerHTML = error;
			return;
		}

		block.innerHTML = getDefaultTemplate( data, deletedElements );
	} );
} );
