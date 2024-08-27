import { GravatarQuickEditorCore, Scope } from '@gravatar-com/quick-editor';
import trackEvent from '../shared/analytics';

const UPDATE_DELAY = 2000;
const LOADING_CLASS = 'avatar-loading';

function updateAvatars() {
	const images: NodeListOf< HTMLImageElement > = document.querySelectorAll(
		'.gravatar-hovercard__avatar, #wp-admin-bar-my-account .avatar'
	);

	// Make all the avatars start pulsating
	images.forEach( ( img ) => {
		img.classList.add( LOADING_CLASS );
	} );

	// Wait a bit and then update the URL
	setTimeout( () => {
		const cacheFlush = '&t=' + new Date().getTime();

		images.forEach( ( img ) => {
			img.src = img.src.replace( /&t=\d*/, cacheFlush );
			img.srcset = img.srcset.replace( / /, cacheFlush + ' ' );
			img.classList.remove( LOADING_CLASS );
		} );
	}, UPDATE_DELAY );
}

export default function showQuickEditor( email: string, locale: string, scope: Scope, updateProfile: () => void ) {
	const quickEditor = new GravatarQuickEditorCore( {
		email,
		scope,
		locale,
		onProfileUpdated: ( type ) => {
			if ( type === 'avatar_updated' ) {
				trackEvent( 'gravatar_enhanced_qe_avatar_updated' );
				updateAvatars();
			} else if ( type === 'profile_updated' ) {
				trackEvent( 'gravatar_enhanced_qe_profile_updated' );
				updateProfile();
			}
		},
	} );

	quickEditor.open();
}
