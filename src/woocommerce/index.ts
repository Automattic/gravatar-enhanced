import { GravatarQuickEditorCore, Scope, ProfileUpdatedType } from '@gravatar-com/quick-editor';
import './style.scss';

interface GeWooCommerce {
    email: string;
    locale: string;
}

declare const geWooCommerce: GeWooCommerce;

const UPDATE_DELAY = 4000;
const LOADING_CLASS = 'avatar-loading';
const AVATAR_SELECTOR = '.woocommerce-account-gravatar__avatar';
const EDIT_BUTTON_SELECTOR = '.woocommerce-account-gravatar__edit';

/**
 * Updates a URL by adding or updating the cache-busting parameter.
 *
 * @param {string} url The original URL.
 * @return {string} The updated URL with the cache-busting parameter.
 */
function updateUrlWithCacheBuster( url: string ): string {
    const urlObj = new URL( url, window.location.origin );
    urlObj.searchParams.set( 't', Date.now().toString() );

    return urlObj.toString();
}

/**
 * Updates all avatar images by adding a cache-busting parameter to force reload.
 */
function updateAvatars(): void {
    const images: NodeListOf<HTMLImageElement> = document.querySelectorAll(
        AVATAR_SELECTOR
    );

    // Add loading class to all avatars.
    images.forEach( ( img ) => {
        img.classList.add( LOADING_CLASS );
    } );

    // Wait and then update the URLs.
    setTimeout( () => {
        images.forEach( ( img ) => {
            // Update img.src.
            img.src = updateUrlWithCacheBuster( img.src );

            // Update img.srcset.
            if ( img.srcset ) {
                img.srcset = img.srcset
                    .split( ',' )
                    .map( ( src ) => {
                        const [ url, descriptor ] = src.trim().split( ' ' );
                        const updatedUrl = updateUrlWithCacheBuster( url );

                        return descriptor
                            ? `${ updatedUrl } ${ descriptor }`
                            : updatedUrl;
                    } )
                    .join( ', ' );
            }

            img.classList.remove( LOADING_CLASS );
        } );
    }, UPDATE_DELAY );
}

/**
 * Initializes the Gravatar Quick Editor and sets up event listeners.
 */
function initGravatarEditor(): void {
    if ( typeof geWooCommerce === 'undefined' || ! geWooCommerce ) {
        console.error(
            'geWooCommerce is not defined. Ensure wp_localize_script is set up correctly.'
        );
        return;
    }

    const editButton = document.querySelector<HTMLAnchorElement>(
        EDIT_BUTTON_SELECTOR
    );

    if ( ! editButton ) {
        console.warn(
            `Edit button not found using selector: ${ EDIT_BUTTON_SELECTOR }`
        );
        return;
    }

    /**
     * Opens the Gravatar Quick Editor with the specified scope.
     *
     * @param {Scope} scope The scopes to edit.
     */
    const openEditor = ( scope: Scope ): void => {
        const quickEditor = new GravatarQuickEditorCore( {
            email: geWooCommerce.email,
            locale: geWooCommerce.locale,
            scope,
            onProfileUpdated: ( type: ProfileUpdatedType ) => {
                if ( type === 'avatar_updated' ) {
                    updateAvatars();
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
