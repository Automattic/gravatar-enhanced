const UPDATE_DELAY = 2000;
const LOADING_CLASS = 'avatar-loading';

let timer = null;

export default function updateAvatars( avatarSelector: string ) {
	const images: NodeListOf< HTMLImageElement > = document.querySelectorAll( avatarSelector );

	// Make all the avatars start pulsating
	images.forEach( ( img ) => {
		img.classList.add( LOADING_CLASS );
	} );

	clearTimeout( timer );

	// Wait a bit and then update the URL
	timer = setTimeout( () => {
		images.forEach( ( img ) => {
			const params = new URLSearchParams( img.src.indexOf( '?' ) === -1 ? '' : img.src.split( '?' )[ 1 ] );

			params.set( 't', new Date().getTime().toString() );

			img.src = img.src.split( '?' )[ 0 ] + '?' + params.toString();

			if ( img.srcset ) {
				const cacheFlush = '&t=' + new Date().getTime();
				img.srcset = img.srcset.replace( / /, cacheFlush + ' ' );
			}

			img.classList.remove( LOADING_CLASS );
		} );

		timer = null;
	}, UPDATE_DELAY );
}
