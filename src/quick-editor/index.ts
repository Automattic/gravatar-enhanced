import { GravatarQuickEditorCore } from '@gravatar-com/quick-editor';
import './style.scss';

const UPDATE_DELAY = 2000;
const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';

declare var geQuickEditor: {
	locale: string,
	email: string,
	hash: string,
};

function attachQuickEditor( email, locale ) {
	const img : HTMLImageElement | null = document.querySelector( '.user-profile-picture img.avatar' );
	const editButton = document.getElementById( 'edit-gravatar' );

	// Check we have all the things
	if ( ! img || ! img.getAttribute( 'srcset' ) || ! editButton ) {
		return;
	}

	const srcSet = img.getAttribute( 'srcset' );
	if ( ! srcSet ) {
		return;
	}

	const imgSrcset = srcSet.replace( /&t=[0-9]+/, '' );
	const quickEditor = new GravatarQuickEditorCore( {
		email,
		scope: [ 'avatars' ],
		locale,
		onProfileUpdated: ( type ) => {
			if ( type !== 'avatar_updated' ) {
				return;
			}

			const avatarURL = new URL( img.src );

			avatarURL.searchParams.set( 't', new Date().getTime().toString() );
			img.classList.add( 'avatar-loading' );

			setTimeout( () => {
				img.src = avatarURL.toString();
				img.srcset = imgSrcset.replace( / /, '&t=' + new Date().getTime() + ' ' );
				img.classList.remove( 'avatar-loading' );
			}, UPDATE_DELAY );
		},
	} );

	editButton.addEventListener( 'click', () => {
		quickEditor.open();
	} );
}

document.addEventListener( 'DOMContentLoaded', () => {
	if ( ! geQuickEditor ) {
		return;
	}

	const { locale, email, hash } = geQuickEditor;

	attachQuickEditor( email, locale );
} );
