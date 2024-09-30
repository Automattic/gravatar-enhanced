import { fetchProfile } from '../utils';

document.addEventListener( 'DOMContentLoaded', () => {
	const test = document.querySelector( '.gravatar-block' );
	const attrs = JSON.parse( test.dataset.attrs );
	console.log( 'LOG ===> ', attrs );
} );
