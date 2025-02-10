import { __ } from '@wordpress/i18n';
import type { MainEditAttrs } from './shared-types';
import { Layout } from './shared-types';
import { getDefaultTemplate, getPortraitTemplate } from './view-templates';
import { fetchProfile, getAvatarUrlWithSize } from './utils';

import './shared.scss';
import './view.scss';

interface Attrs extends Omit< MainEditAttrs, 'userType' | 'userEmail' > {
	hashedEmail: string;
}

document.addEventListener( 'DOMContentLoaded', () => {
	const gravatarBlocks = document.querySelectorAll< HTMLDivElement >( '.gravatar-block' );

	gravatarBlocks.forEach( async ( block ) => {
		const {
			layout,
			avatarUrlSizeParam,
			placeholderProfile: placeholderProfileData,
			hashedEmail = '',
			deletedElements = {},
		} = JSON.parse( block.dataset.attrs ) as Attrs;

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

		const placeholderProfile = placeholderProfileData ? getTemplate( placeholderProfileData, deletedElements ) : '';

		block.innerHTML = `
			${ ! placeholderProfile ? `<div class="gravatar-block__loading">${ __( 'Loading…', 'gravatar-enhanced' ) }</div>` : '' }
			${ placeholderProfile }
		`;

		if ( ! block.dataset.attrs ) {
			block.innerHTML = `
				<div class="gravatar-block__error">${ __( 'Oops! Something went wrong', 'gravatar-enhanced' ) }</div>
				${ placeholderProfile }
			`;

			return;
		}

		const { error, data } = await fetchProfile( hashedEmail );

		if ( error ) {
			block.innerHTML = `
				<div class="gravatar-block__error">${ error }</div>
				${ placeholderProfile }
			`;

			return;
		}

		data.avatar_url = getAvatarUrlWithSize( data.avatar_url, avatarUrlSizeParam || defaultAvatarSize );

		block.innerHTML = getTemplate( data, deletedElements );
	} );
} );
