import { __ } from '@wordpress/i18n';
import type { MainEditAttrs } from './shared-types';
import { getDefaultTemplate, getPortraitTemplate } from './view-templates';
import { fetchProfile, getAvatarUrlWithSize } from './utils';

import './shared.scss';
import './view.scss';

interface Attrs {
	layout: MainEditAttrs[ 'layout' ];
	avatarUrlSizeParam: MainEditAttrs[ 'avatarUrlSizeParam' ];
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

		const {
			layout,
			avatarUrlSizeParam,
			hashedEmail = '',
			deletedElements = {},
		} = JSON.parse( block.dataset.attrs ) as Attrs;

		const { error, data } = await fetchProfile( hashedEmail );

		if ( error ) {
			block.innerHTML = error;
			return;
		}

		let templateFn = getDefaultTemplate;
		let defaultAvatarSize = 72;

		switch ( layout ) {
			case 'portrait':
				templateFn = getPortraitTemplate;
				defaultAvatarSize = 354;
				break;
			case 'landscape':
				// TODO: Implement landscape layout...
				break;
			case 'line':
				// TODO: Implement line layout...
				break;
		}

		data.avatar_url = getAvatarUrlWithSize( data.avatar_url, avatarUrlSizeParam || defaultAvatarSize );

		block.innerHTML = templateFn( data, deletedElements );
	} );
} );
