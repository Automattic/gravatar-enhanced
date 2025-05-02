import { Hovercards } from '@gravatar-com/hovercards';
import { convertJsonToUser } from '../hovercards/utils';
import { GravatarQuickEditorCore } from '@gravatar-com/quick-editor';
import trackEvent from '../shared/analytics';
import updateAvatars from '../shared/update-avatars';
import type { ProfileData } from '@gravatar-com/hovercards';

const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';

function createHovercard( user: ProfileData ) {
	const container = document.querySelector( '.gravatar-hovercard-container' );
	const loadingHovercard = document.querySelectorAll( '.gravatar-profile__loading' );

	if ( ! container || loadingHovercard.length === 0 ) {
		return;
	}

	const hovercard = Hovercards.createHovercard( user );

	// Replace PHP hovercard with JS hovercard
	loadingHovercard.forEach( ( el ) => {
		if ( el.parentNode ) {
			el.parentNode.removeChild( el );
		}
	} );
	container.appendChild( hovercard );
}

function copyVerifiedLinks() {
	// Copy verified links
	const verifiedLinks = document.querySelector( '.gravatar-hovercard-container .gravatar-hovercard__social-links' );
	if ( ! verifiedLinks ) {
		return;
	}

	const clonedVerifiedLinks = verifiedLinks.cloneNode( true );
	const target = document.querySelector( '.user-verified-services td .description' );

	if ( target ) {
		target.parentNode.insertBefore( clonedVerifiedLinks, target );
	}
}

function showValidProfile( avatarUrl: string, profile ) {
	const user = convertJsonToUser( profile, avatarUrl );

	// Remove existing hovercard and links
	document
		.querySelectorAll(
			'.user-verified-services .gravatar-hovercard__social-links, .gravatar-hovercard-container .gravatar-hovercard'
		)
		.forEach( ( el ) => {
			if ( el.parentNode ) {
				el.parentNode.removeChild( el );
			}
		} );

	createHovercard( user );
	copyVerifiedLinks();
}

function showProbablyNoProfile( avatarUrl: string, text: QuickEditorText, canEdit: boolean ) {
	const user = {
		displayName: canEdit ? text.unknownTitle : text.otherUnknownTitle,
		description: canEdit ? text.unknownDescription : text.otherUnknownDescription,
		avatarUrl,
		hash: '',
		profileUrl: '',
	};

	createHovercard( user );
	copyVerifiedLinks();
}

function showError( hash: string, avatarUrl: string, text: QuickEditorText ) {
	const user = {
		displayName: text.errorTitle,
		description: text.errorDescription,
		hash,
		avatarUrl,
		profileUrl: 'https://gravatar.com/' + hash,
	};

	createHovercard( user );
	copyVerifiedLinks();
}

async function fetchUserProfile( hash, avatar, text, canEdit ) {
	try {
		// Get profile data
		const response = await fetch( `${ BASE_API_URL }/${ hash }?source=hovercard` );
		if ( ! response.ok ) {
			showProbablyNoProfile( avatar, text, canEdit );
			return;
		}

		const profile = await response.json();

		showValidProfile( avatar, profile );
	} catch ( error ) {
		// eslint-disable-next-line no-console
		console.error( error );

		showError( hash, avatar, text );
	}
}

export default function checkUserProfile( { locale, email, hash, avatar, text, canEdit }: QuickEditor ) {
	const container = document.querySelector( '.gravatar-profile__loading' );
	if ( ! container ) {
		return;
	}

	let quickEditor = null;

	const openEditor = ( scope ) => {
		if ( quickEditor ) {
			quickEditor.close();
		}

		quickEditor = new GravatarQuickEditorCore( {
			email,
			scope,
			locale,
			onProfileUpdated: ( type ) => {
				if ( type === 'avatar_updated' ) {
					trackEvent( 'gravatar_enhanced_qe_avatar_updated' );
					updateAvatars(
						'.gravatar-hovercard__avatar, #wp-admin-bar-my-account .avatar, .user-profile-picture img'
					);
				} else if ( type === 'profile_updated' ) {
					trackEvent( 'gravatar_enhanced_qe_profile_updated' );
					fetchProfile();
				}
			},
		} );

		quickEditor.open();
	};
	const fetchProfile = () => fetchUserProfile( hash, avatar, text, canEdit );

	fetchProfile();

	const classMap = [
		{ selector: '.user-profile-picture img, .user-profile-picture a', scope: [ 'avatars' ] },
		{ selector: '.user-gravatar-card button', scope: [] },
		{ selector: '.user-verified-services .description a', scope: [ 'verified-accounts' ] },
	];

	classMap.forEach( ( { selector, scope } ) => {
		const elements = document.querySelectorAll( selector );
		elements.forEach( ( element ) => {
			element.addEventListener( 'click', ( ev ) => {
				ev.preventDefault();
				ev.stopPropagation();

				openEditor( scope );
			} );
		} );
	} );
}
