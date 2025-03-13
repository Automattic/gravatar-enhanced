import { Hovercards } from '@gravatar-com/hovercards';
import { convertJsonToUser } from '../hovercards/utils';
import { GravatarQuickEditorCore } from '@gravatar-com/quick-editor';
import trackEvent from '../shared/analytics';
import updateAvatars from '../shared/update-avatars';
import type { ProfileData } from '@gravatar-com/hovercards';
import type { Scope } from '@gravatar-com/quick-editor';

interface ActionScope {
	[ name: string ]: Scope;
}
const SELECTOR_HIDDEN = 'gravatar-profile__hidden';
const SELECTOR_DESCRIPTION = '#gravatar-profile-sync__description';
const SELECTOR_WP_BIO = '#description';
const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';
const ACTIONS_VALID: ActionScope = {
	'.gravatar-hovercard__profile-link': [ 'about', 'avatars' ],
	'.gravatar-hovercard__avatar': [ 'avatars' ],
};
const ACTIONS_UNKNOWN: ActionScope = {
	...ACTIONS_VALID,
	'.gravatar-hovercard__name': [ 'about', 'avatars' ],
	'.gravatar-hovercard__social-link': [ 'about', 'avatars' ],
};

function createHovercard(
	user: ProfileData,
	buttonText: string,
	actionSelectors: ActionScope,
	actionCallback: ( scope: Scope ) => void,
	canEdit = true
) {
	const options = {
		i18n: {
			'View profile →': buttonText,
		},
		additionalClass: canEdit ? 'gravatar-hovercard--editable' : '',
	};

	const container = document.querySelector( '.gravatar-hovercard-container' );
	const loadingHovercard = document.querySelector( '.gravatar-profile__loading' );

	if ( ! container || ! loadingHovercard ) {
		return;
	}

	const hovercard = Hovercards.createHovercard( user, options );

	Object.keys( actionSelectors ).forEach( ( selector ) => {
		const actions = hovercard.querySelectorAll( selector );

		actions.forEach( ( action ) => {
			action.addEventListener( 'click', ( ev ) => {
				ev.preventDefault();

				if ( canEdit ) {
					actionCallback( actionSelectors[ selector ] );
				}
			} );
		} );
	} );

	// Replace PHP hovercard with JS hovercard
	container.removeChild( loadingHovercard );
	container.appendChild( hovercard );
}

function showValidProfile( avatarUrl: string, profile, buttonText: string, openEditor: ( scopes: Scope ) => void ) {
	const user = convertJsonToUser( profile, avatarUrl );

	createHovercard( user, buttonText, ACTIONS_VALID, openEditor );

	// Show any sync details
	if ( profile.description !== document.querySelector( SELECTOR_WP_BIO ).textContent ) {
		const syncDetails = document.querySelector( SELECTOR_DESCRIPTION );
		const wpBio: HTMLButtonElement = document.querySelector( SELECTOR_WP_BIO );

		if ( syncDetails && wpBio ) {
			syncDetails.classList.remove( SELECTOR_HIDDEN );
			wpBio.dataset.description = profile.description;
		}
	}
}

function showProbablyNoProfile(
	avatarUrl: string,
	text: QuickEditorText,
	canEdit,
	openEditor: ( scopes: Scope ) => void
) {
	const user = {
		displayName: canEdit ? text.unknownTitle : text.otherUnknownTitle,
		description: canEdit ? text.unknownDescription : text.otherUnknownDescription,
		avatarUrl,
		hash: '',
		profileUrl: '',
	};

	createHovercard( user, canEdit ? text.createButton : '', ACTIONS_UNKNOWN, openEditor, canEdit );

	const signupBio = document.querySelector( '.gravatar-signup-bio' );
	if ( signupBio ) {
		signupBio.classList.remove( SELECTOR_HIDDEN );
	}
}

function showError( text: QuickEditorText ) {
	const user = {
		displayName: text.errorTitle,
		description: text.errorDescription,
		hash: '',
		avatarUrl: '',
		profileUrl: '',
	};

	createHovercard( user, '', ACTIONS_UNKNOWN, () => {} );
}

function setupSync() {
	const syncButton = document.querySelector( SELECTOR_DESCRIPTION + ' button' );
	const syncDetails = document.querySelector( SELECTOR_DESCRIPTION );
	const wpBio: HTMLButtonElement = document.querySelector( SELECTOR_WP_BIO );

	if ( ! syncButton || ! syncDetails || ! wpBio ) {
		return;
	}

	syncButton.addEventListener( 'click', () => {
		wpBio.textContent = wpBio.dataset.description;
		syncDetails.classList.add( SELECTOR_HIDDEN );
	} );
}

async function fetchUserProfile( hash, avatar, text, openEditor, canEdit ) {
	try {
		// Get profile data
		const response = await fetch( `${ BASE_API_URL }/${ hash }?source=hovercard` );
		if ( ! response.ok ) {
			showProbablyNoProfile( avatar, text, canEdit, openEditor );
			return;
		}

		const profile = await response.json();

		showValidProfile( avatar, profile, canEdit ? text.updateButton : text.viewButton, openEditor );
	} catch ( error ) {
		// eslint-disable-next-line no-console
		console.error( error );

		showError( text );
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
					updateAvatars( '.gravatar-hovercard__avatar, #wp-admin-bar-my-account .avatar' );
				} else if ( type === 'profile_updated' ) {
					trackEvent( 'gravatar_enhanced_qe_profile_updated' );
					fetchProfile();
				}
			},
		} );

		quickEditor.open();
	};
	const fetchProfile = () => fetchUserProfile( hash, avatar, text, openEditor, canEdit );

	setupSync();
	fetchProfile();
}
