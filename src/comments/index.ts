import { sha256 } from 'js-sha256';
import { GravatarQuickEditorCore } from '@gravatar-com/quick-editor';
import { Hovercards } from '@gravatar-com/hovercards';
import trackEvent from '../shared/analytics';
import updateAvatars from '../shared/update-avatars';
import isEmail from '../shared/is-email';
import { adjustGravatarPosition, fetchUserProfile, suggestProfile, hideProfile, showProfile } from './profile';
import { GRAVATAR_CONTAINER, COMMENT_EMAIL_WRAPPER, COMMENT_EMAIL_FIELD } from './constants';
import './style.scss';

const INPUT_TIMEOUT = 1000;

document.addEventListener( 'DOMContentLoaded', () => {
	const email = document.querySelector( COMMENT_EMAIL_FIELD ) as HTMLInputElement;
	const qeButton = document.querySelector( GRAVATAR_CONTAINER + ' img' );
	let lastRequestEmail = '';
	let lastRequestUrl = '';
	let lastRequestName = '';
	let debounceProfileTimeout: NodeJS.Timeout;
	let quickEditor = null;

	const hovercards = new Hovercards( {
		onCanShowHovercard: () => {
			return quickEditor === null || ! quickEditor.isOpen();
		},
	} );

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

		const profile = await fetchUserProfile( emailValue );

		lastRequestEmail = emailValue;

		if ( profile ) {
			suggestProfile( profile, lastRequestUrl, lastRequestName );
			showProfile( profile, hovercards );

			lastRequestUrl = profile.profile_url;
			lastRequestName = profile.display_name;
		} else {
			showProfile(
				{
					display_name: '',
					profile_url: '',
					avatar_url: 'https://gravatar.com/avatar/' + sha256( emailValue.trim().toLowerCase() ),
				},
				hovercards
			);
		}
	};

	email?.addEventListener( 'blur', loadProfile );
	email?.addEventListener( 'input', ( ev ) => {
		clearTimeout( debounceProfileTimeout );
		debounceProfileTimeout = setTimeout( () => loadProfile( ev ), INPUT_TIMEOUT );

		// If the email is changed then close any QE and clear the instance
		if ( quickEditor ) {
			quickEditor.close();
			quickEditor = null;
		}
	} );

	// Hook up the image to the QE
	qeButton?.addEventListener( 'click', () => {
		if ( ! quickEditor ) {
			quickEditor = new GravatarQuickEditorCore( {
				email: email?.value || gravatarEnhancedComments?.email || '',
				scope: [ 'avatars' ],
				locale: gravatarEnhancedComments?.locale || 'en',
				onProfileUpdated: () => {
					trackEvent( 'gravatar_enhanced_qe_avatar_updated' );
					updateAvatars( GRAVATAR_CONTAINER + ' img' );
				},
			} );
		}

		quickEditor.open();
	} );

	// Reposition the avatar on resize - it can get slightly out of place
	const resizeObserver = new ResizeObserver( () => {
		if ( lastRequestEmail ) {
			window.requestAnimationFrame( () => {
				adjustGravatarPosition();
			} );
		}
	} );

	const emailContainer = document.querySelector( COMMENT_EMAIL_WRAPPER ) as HTMLInputElement;
	if ( emailContainer ) {
		resizeObserver.observe( emailContainer );
	}
} );
