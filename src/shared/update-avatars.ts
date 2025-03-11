const UPDATE_DELAY = 2000;
const LOADING_CLASS = 'avatar-loading';

export default function updateAvatars( avatarSelector ) {
	const images: NodeListOf< HTMLImageElement > = document.querySelectorAll( avatarSelector );

	// Make all the avatars start pulsating
	images.forEach( ( img ) => {
		img.classList.add( LOADING_CLASS );
	} );

	// Wait a bit and then update the URL
	setTimeout( () => {
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
	}, UPDATE_DELAY );
}
