import { __ } from '@wordpress/i18n';
import type { MainEditAttrs } from './shared-types';
import { UserTypes, Layout } from './shared-types';
import { getDefaultTemplate, getPortraitTemplate } from './view-templates';
import { fetchProfile, getAvatarUrlWithSize } from './utils';

import './shared.scss';
import './view.scss';

interface Attrs extends Omit< MainEditAttrs, 'userEmail' > {
	hashedEmail: string;
}

document.addEventListener( 'DOMContentLoaded', () => {
	const gravatarBlocks = document.querySelectorAll< HTMLDivElement >( '.gravatar-block' );

	gravatarBlocks.forEach( async ( block ) => {
		const {
			userType,
			layout,
			avatarUrlSizeParam,
			placeholderProfile,
			hashedEmail = '',
			deletedElements = {},
		} = JSON.parse( block.dataset.attrs ) as Attrs;

		// If the `userType` is email, but the email is not provided, skip the block.
		if ( userType === UserTypes.EMAIL && ! hashedEmail ) {
			return;
		}

		let getTemplate = getDefaultTemplate;
		let defaultAvatarSize = 72;

		switch ( layout ) {
			case Layout.PORTRAIT:
				getTemplate = getPortraitTemplate;
				defaultAvatarSize = 354;
				break;
			case Layout.LANDSCAPE:
				// TODO: Implement landscape layout...
				break;
			case Layout.LINE:
				// TODO: Implement line layout...
				break;
		}

		block.innerHTML = getTemplate( placeholderProfile, deletedElements );

		if ( ! block.dataset.attrs ) {
			block.innerHTML += `
				<div class="gravatar-block__status">${ __( 'Oops! Something went wrong', 'gravatar-enhanced' ) }</div>
			`;

			return;
		}

		const { error, data } = await fetchProfile( hashedEmail );

		if ( error ) {
			block.innerHTML += `
				<div class="gravatar-block__status">${ error }</div>
			`;

			return;
		}

		data.avatar_url = getAvatarUrlWithSize( data.avatar_url, avatarUrlSizeParam || defaultAvatarSize );

		block.innerHTML = getTemplate( data, deletedElements );
	} );
} );
