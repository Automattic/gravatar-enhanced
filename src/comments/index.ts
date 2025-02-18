import { sha256 } from 'js-sha256';
import showQuickEditor from '../shared/show-quick-editor';
import { Hovercards } from '@gravatar-com/hovercards';
import './style.scss';

const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';
const GRAVATAR_CONTAINER = '.gravatar-enhanced-comments';
const COMMENT_EMAIL_FIELD = '#email';
const INPUT_TIMEOUT = 1000;

const hovercards = new Hovercards();

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

function showProfile( profile ) {
	const gravatarProfile = document.querySelector( GRAVATAR_CONTAINER );
	const gravatarImg = document.querySelector( GRAVATAR_CONTAINER + ' img' ) as HTMLImageElement;

	if ( ! gravatarProfile || ! gravatarImg ) {
		return;
	}

	gravatarImg.src = profile.avatar_url;
	gravatarProfile.classList.remove( 'gravatar-enhanced-comments--hidden' );

	// Hook up to hovercard
	hovercards.attach( gravatarImg );
}

document.addEventListener( 'DOMContentLoaded', () => {
	const email = document.querySelector( COMMENT_EMAIL_FIELD ) as HTMLInputElement;
	const qeButton = document.querySelector( GRAVATAR_CONTAINER + ' button' );
	let lastRequestEmail = '';
	let debounceTimeout: NodeJS.Timeout;

	const loadProfile = async ( event ) => {
		const emailValue = ( event.target as HTMLInputElement ).value;
		if ( emailValue === lastRequestEmail ) {
			return;
		}

		toggleLoading( true );

		const profile = await fetchUserProfile( emailValue );

		toggleLoading( false );

		lastRequestEmail = emailValue;

		if ( profile ) {
			suggestProfile( profile );
			showProfile( profile );
		} else {
			showProfile( {
				display_name: '',
				profile_url: '',
				avatar_url: 'https://gravatar.com/avatar/' + sha256( emailValue.trim().toLowerCase() ),
			} );
		}
	};

	email?.addEventListener( 'blur', loadProfile );
	email?.addEventListener( 'blur', loadProfile );

	email?.addEventListener( 'input', ( ev ) => {
		clearTimeout( debounceTimeout );
		debounceTimeout = setTimeout( () => loadProfile( ev ), INPUT_TIMEOUT );
	} );

	qeButton?.addEventListener( 'click', () =>
		showQuickEditor(
			email?.value || gravatarEnhancedComments?.email || '',
			gravatarEnhancedComments?.locale || 'en',
			[ 'avatars' ],
			GRAVATAR_CONTAINER + ' img',
			() => {}
		)
	);
} );
