import type { MainEditAttrs } from './shared-types';
import { getDefaultTemplate, getPortraitTemplate } from './view-templates';
import { fetchProfile } from './utils';
import { __ } from '@wordpress/i18n';

import './shared.scss';
import './view.scss';

interface Attrs {
	layout: MainEditAttrs[ 'layout' ];
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

		const { layout, hashedEmail = '', deletedElements = {} } = JSON.parse( block.dataset.attrs ) as Attrs;

		const { error, data } = await fetchProfile( hashedEmail );

		if ( error ) {
			block.innerHTML = error;
			return;
		}

		let template = getDefaultTemplate( data, deletedElements );

		switch ( layout ) {
			case 'portrait':
				template = getPortraitTemplate( data, deletedElements );
				break;
			case 'landscape':
				// TODO: Implement landscape layout...
				break;
			case 'line':
				// TODO: Implement line layout...
				break;
		}

		block.innerHTML = template;
	} );
} );
