import { GravatarQuickEditorCore, Scope, ProfileUpdatedType } from '@gravatar-com/quick-editor';
import updateAvatars from '../shared/update-avatars';
import './style-my-account.scss';

const AVATAR_SELECTOR = '.woocommerce-account-gravatar__avatar';
const EDIT_BUTTON_SELECTOR = '.woocommerce-account-gravatar__edit-wrapper';
const UPDATE_DELAY = 4000;

/**
 * Initializes the Gravatar Quick Editor and sets up event listeners.
 */
function initGravatarEditor(): void {
	if ( typeof geWcMyAccount === 'undefined' || ! geWcMyAccount ) {
		return;
	}

	const editButton = document.querySelector< HTMLAnchorElement >( EDIT_BUTTON_SELECTOR );

	if ( ! editButton ) {
		return;
	}

	/**
	 * Opens the Gravatar Quick Editor with the specified scope.
	 *
	 * @param {Scope} scope The scopes to edit.
	 */
	const openEditor = ( scope: Scope ): void => {
		const quickEditor = new GravatarQuickEditorCore( {
			email: geWcMyAccount.email,
			locale: geWcMyAccount.locale,
			scope,
			onProfileUpdated: ( type: ProfileUpdatedType ) => {
				if ( type === 'avatar_updated' ) {
					updateAvatars( { selector: AVATAR_SELECTOR, delay: UPDATE_DELAY } );
				}
			},
		} );

		quickEditor.open();
	};

	editButton.addEventListener( 'click', ( event ) => {
		event.preventDefault();
		openEditor( [ 'avatars' ] );
	} );
}

document.addEventListener( 'DOMContentLoaded', () => {
	initGravatarEditor();
} );
