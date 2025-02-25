import { sha256 } from 'js-sha256';
import showQuickEditor from '../shared/show-quick-editor';
import { Hovercards } from '@gravatar-com/hovercards';
import './style.scss';

const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';
const GRAVATAR_CONTAINER = '.gravatar-enhanced-profile';
const COMMENT_EMAIL_FIELD = '#email';
const INPUT_TIMEOUT = 1000;
const DEBOUNCE_TIMEOUT = 250;

const hovercards = new Hovercards();

function isEmail( email ) {
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	return emailRegex.test( email );
}

function toggleLoading( isLoading ) {
	const author = document.getElementById( 'author' ) as HTMLInputElement;
	const url = document.getElementById( 'url' ) as HTMLInputElement;
	const email = document.querySelector( COMMENT_EMAIL_FIELD ) as HTMLInputElement;

	email?.classList.toggle( 'gravatar-enhanced-is-loading', isLoading );
	author?.classList.toggle( 'gravatar-enhanced-is-loading', isLoading );
	url?.classList.toggle( 'gravatar-enhanced-is-loading', isLoading );
}

async function fetchUserProfile( email ) {
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

function suggestProfile( profile ) {
	const author = document.getElementById( 'author' ) as HTMLInputElement;
	const url = document.getElementById( 'url' ) as HTMLInputElement;

	if ( author && author.value === '' ) {
		author.value = profile.display_name;
	}

	if ( url && url.value === '' ) {
		url.value = profile.profile_url;
	}
}

function hideProfile() {
	const emailContainer = document.querySelector( '.comment-form-email' ) as HTMLInputElement;
	const emailField = document.querySelector( COMMENT_EMAIL_FIELD ) as HTMLInputElement;

	if ( ! emailField || ! emailContainer ) {
		return;
	}

	emailContainer.classList.remove( 'gravatar-enhanced-comments' );
	emailField.style.paddingLeft = '';
}

function adjustGravatarPosition() {
	const gravatarProfile = document.querySelector( GRAVATAR_CONTAINER ) as HTMLElement;
	const emailContainer = document.querySelector( '.comment-form-email' ) as HTMLInputElement;
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
	const height = Math.round( emailFieldRect.height * 0.8 );
	const heightDifference = emailFieldRect.height - height;
	const leftOffset = Math.round( leftRectOffset + padding / 2 ) + parseInt( computedStyle.borderLeftWidth );
	const topOffset = Math.round( topRectOffset + heightDifference / 2 );

	// Position the Gravatar inside the text field
	gravatarProfile.style.height = height + 'px';
	gravatarProfile.style.width = height + 'px';
	gravatarProfile.style.top = topOffset + 'px';
	gravatarProfile.style.left = leftOffset + 'px';

	// Move the text up to allow the Gravatar to fit
	emailField.style.paddingLeft = Math.round( height + padding * 1.3 ) + 'px';
}

function showProfile( profile, isShowingEditor ) {
	const gravatarImg = document.querySelector( GRAVATAR_CONTAINER + ' img' ) as HTMLImageElement;
	const emailContainer = document.querySelector( '.comment-form-email' ) as HTMLInputElement;

	if ( ! gravatarImg || ! emailContainer ) {
		return;
	}

	gravatarImg.src = profile.avatar_url;
	emailContainer.classList.add( 'gravatar-enhanced-comments' );

	adjustGravatarPosition();

	// Hook up to hovercard
	hovercards.attach( gravatarImg, {
		onCanShowHovercard: () => {
			return ! isShowingEditor();
		},
	} );
}

document.addEventListener( 'DOMContentLoaded', () => {
	const email = document.querySelector( COMMENT_EMAIL_FIELD ) as HTMLInputElement;
	const qeButton = document.querySelector( GRAVATAR_CONTAINER + ' img' );
	let lastRequestEmail = '';
	let debounceProfileTimeout: NodeJS.Timeout;
	let debounceResizeTimeout: NodeJS.Timeout;
	let isShowingEditor = false;

	const loadProfile = async ( event ) => {
		clearTimeout( debounceProfileTimeout );

		const emailValue = ( event.target as HTMLInputElement ).value;
		if ( emailValue === lastRequestEmail ) {
			return;
		}

		if ( ! isEmail( emailValue ) ) {
			lastRequestEmail = '';
			hideProfile();
			return;
		}

		toggleLoading( true );

		const profile = await fetchUserProfile( emailValue );

		toggleLoading( false );

		lastRequestEmail = emailValue;

		if ( profile ) {
			suggestProfile( profile );
			showProfile( profile, () => isShowingEditor );
		} else {
			showProfile(
				{
					display_name: '',
					profile_url: '',
					avatar_url: 'https://gravatar.com/avatar/' + sha256( emailValue.trim().toLowerCase() ),
				},
				() => isShowingEditor
			);
		}
	};

	email?.addEventListener( 'blur', loadProfile );
	email?.addEventListener( 'input', ( ev ) => {
		clearTimeout( debounceProfileTimeout );
		debounceProfileTimeout = setTimeout( () => loadProfile( ev ), INPUT_TIMEOUT );
	} );

	// Hook up the image to the QE
	qeButton?.addEventListener( 'click', () => {
		isShowingEditor = true;

		showQuickEditor(
			email?.value || gravatarEnhancedComments?.email || '',
			gravatarEnhancedComments?.locale || 'en',
			[ 'avatars' ],
			GRAVATAR_CONTAINER + ' img',
			() => {
				isShowingEditor = false;
			}
		);
	} );

	// Reposition the avatar on resize - it can get slightly out of place
	window.addEventListener( 'resize', () => {
		clearTimeout( debounceResizeTimeout );

		debounceProfileTimeout = setTimeout( () => {
			if ( lastRequestEmail ) {
				adjustGravatarPosition();
			}
		}, DEBOUNCE_TIMEOUT );
	} );
} );
