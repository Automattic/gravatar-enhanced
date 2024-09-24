import { GravatarQuickEditorCore } from '@gravatar-com/quick-editor';
import './style.scss';

const UPDATE_DELAY = 2000;
const LOADING_CLASS = 'avatar-loading';

function updateAvatars() {
	const images: NodeListOf< HTMLImageElement > = document.querySelectorAll(
		'.woocommerce-account-gravatar__avatar'
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

document.addEventListener( 'DOMContentLoaded', () => {
	if ( ! geWooCommerce ) {
		return;
	}

	document.querySelector( '.woocommerce-account-gravatar__edit' )?.addEventListener( 'click', () => {
		const quickEditor = new GravatarQuickEditorCore( {
			email: geWooCommerce.email,
			locale: geWooCommerce.locale,
			scope: [ 'avatars' ],
			onProfileUpdated: ( type ) => {
				if ( type === 'avatar_updated' ) {
					updateAvatars();
				}
			},
		} );

		quickEditor.open();
	} );
} );
