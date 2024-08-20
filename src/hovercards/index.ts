import { Hovercards } from '@gravatar-com/hovercards';
import '@gravatar-com/hovercards/dist/style.css';

const ignoreSelector = '#wpadminbar img';

document.addEventListener( 'DOMContentLoaded', () => {
	const hovercards = new Hovercards();

	hovercards.attach( document.body, { ignoreSelector } );
} );
