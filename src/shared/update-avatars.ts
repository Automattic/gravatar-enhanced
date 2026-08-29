interface UpdateAvatarsOptions {
	selector: string;
	delay?: number;
}

const DEFAULT_DELAY = 2000;
const LOADING_CLASS = 'avatar-loading';

let timer = null;

/**
 * Updates all avatar images by adding a cache-busting parameter to force reload.
 *
 * @param {UpdateAvatarsOptions} options Options.
 */
export default function updateAvatars( options: UpdateAvatarsOptions ): void {
	const { selector, delay = DEFAULT_DELAY } = options;
	const images: NodeListOf< HTMLImageElement > = document.querySelectorAll( selector );

	// Make all the avatars start pulsating
	images.forEach( ( img ) => {
		img.classList.add( LOADING_CLASS );
	} );

	clearTimeout( timer );

	// Wait a bit and then update the URL
	timer = setTimeout( () => {
		const timestamp = new Date().getTime().toString();

		images.forEach( ( img ) => {
			// Update img.src.
			const srcParams = new URLSearchParams( img.src.indexOf( '?' ) === -1 ? '' : img.src.split( '?' )[ 1 ] );
			srcParams.set( 't', timestamp );
			img.src = img.src.split( '?' )[ 0 ] + '?' + srcParams.toString();

			// Update img.srcset.
			if ( img.srcset ) {
				img.srcset = img.srcset
					.split( ',' )
					.map( ( src ) => {
						const parts = src.trim().split( / (.*)/ );
						const url = parts[ 0 ];
						const descriptor = parts[ 1 ] || '';

						const urlParams = new URLSearchParams( url.indexOf( '?' ) === -1 ? '' : url.split( '?' )[ 1 ] );
						urlParams.set( 't', timestamp );
						const updatedUrl = url.split( '?' )[ 0 ] + '?' + urlParams.toString();

						return descriptor ? `${ updatedUrl } ${ descriptor }` : updatedUrl;
					} )
					.join( ', ' );
			}

			img.classList.remove( LOADING_CLASS );
		} );

		timer = null;
	}, delay );
}
