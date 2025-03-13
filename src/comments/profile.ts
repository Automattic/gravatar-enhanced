import { sha256 } from 'js-sha256';
import { GRAVATAR_CONTAINER, COMMENT_EMAIL_WRAPPER, COMMENT_EMAIL_FIELD } from './constants';

const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';

export function adjustGravatarPosition() {
	const gravatarProfile = document.querySelector( GRAVATAR_CONTAINER ) as HTMLSpanElement;
	const emailContainer = document.querySelector( COMMENT_EMAIL_WRAPPER ) as HTMLInputElement;
	const emailField = document.querySelector( COMMENT_EMAIL_FIELD ) as HTMLInputElement;

	if ( ! gravatarProfile || ! emailField || ! emailContainer ) {
		return;
	}

	// Measure the email field
	const computedStyle = getComputedStyle( emailField );
	const padding = parseInt( computedStyle.paddingTop, 10 ) + parseInt( computedStyle.borderTopWidth, 10 );
	const emailFieldRect = emailField.getBoundingClientRect();
	const emailContainerRect = emailContainer.getBoundingClientRect();
	const topRectOffset = emailFieldRect.top - emailContainerRect.top;
	const leftRectOffset = emailFieldRect.left - emailContainerRect.left;
	const height = parseFloat( ( emailFieldRect.height * 0.8 ).toFixed( 1 ) );
	const heightDifference = parseFloat( ( emailFieldRect.height - height ).toFixed( 1 ) );
	const leftOffset = parseFloat(
		( leftRectOffset + padding / 2 + parseInt( computedStyle.borderLeftWidth ) ).toFixed( 1 )
	);
	const topOffset = parseFloat( ( topRectOffset + heightDifference / 2 ).toFixed( 1 ) );

	// Position the Gravatar inside the text field
	gravatarProfile.style.height = height + 'px';
	gravatarProfile.style.width = height + 'px';
	gravatarProfile.style.top = topOffset + 'px';
	gravatarProfile.style.left = leftOffset + 'px';

	// Move the text up to allow the Gravatar to fit
	emailField.style.paddingLeft = parseFloat( ( height + padding * 1.3 ).toFixed( 1 ) ) + 'px';
}

export async function fetchUserProfile( email: string ) {
	const hash = sha256( email.trim().toLowerCase() );

	try {
		// Get profile data
		const response = await fetch( `${ BASE_API_URL }/${ hash }?source=hovercard` );
		if ( ! response.ok ) {
			return null;
		}

		return await response.json();
	} catch ( error ) {
		// eslint-disable-next-line no-console
		console.error( error );
	}

	return null;
}

export function suggestProfile( profile, lastUrl, lastName ) {
	const author = document.getElementById( 'author' ) as HTMLInputElement;
	const url = document.getElementById( 'url' ) as HTMLInputElement;
	const profileUrl = profile.profile_url;

	if ( author && ( author.value === '' || author.value === lastName ) ) {
		author.value = profile.display_name;
	}

	if ( url && profileUrl && ( url.value === '' || url.value === lastUrl ) ) {
		url.value = profileUrl;
	}
}

export function hideProfile() {
	const emailContainer = document.querySelector( COMMENT_EMAIL_WRAPPER ) as HTMLInputElement;
	const emailField = document.querySelector( COMMENT_EMAIL_FIELD ) as HTMLInputElement;

	if ( ! emailField || ! emailContainer ) {
		return;
	}

	emailContainer.classList.remove( 'gravatar-enhanced-comments' );
	emailField.style.paddingLeft = '';
}

export function showProfile( profile, hovercards ) {
	const gravatarImg = document.querySelector( GRAVATAR_CONTAINER + ' img' ) as HTMLImageElement;
	const emailContainer = document.querySelector( COMMENT_EMAIL_WRAPPER ) as HTMLInputElement;

	if ( ! gravatarImg || ! emailContainer ) {
		return;
	}

	gravatarImg.src = profile.avatar_url;
	emailContainer.classList.add( 'gravatar-enhanced-comments' );

	adjustGravatarPosition();

	// Hook up to hovercard
	hovercards.attach( gravatarImg );
}
