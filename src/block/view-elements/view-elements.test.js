/* eslint-env jest */
// @ts-nocheck -- Jest globals are provided by wp-scripts at test time.
import getImage from './get-image';
import getLink from './get-link';
import getMaybeLink from './get-maybe-link';

function parseElement( markup ) {
	const template = document.createElement( 'template' );
	template.innerHTML = markup.trim();

	return template.content.firstElementChild;
}

describe( 'profile block view elements', () => {
	it( 'does not create attributes injected through image or link URLs', () => {
		const attack = 'https://example.com/image.png" onerror="alert(1)';
		const element = parseElement(
			getImage( {
				linkUrl: 'https://example.com/profile" onmouseover="alert(1)',
				imageUrl: attack,
				imageWidth: 72,
				imageHeight: 72,
				imageAlt: 'Avatar',
			} )
		);
		const image = element.querySelector( 'img' );

		expect( element.tagName ).toBe( 'A' );
		expect( element.hasAttribute( 'onmouseover' ) ).toBe( false );
		expect( image?.hasAttribute( 'onerror' ) ).toBe( false );
		expect( image?.getAttribute( 'src' ) ).toBe( attack );
	} );

	it( 'rejects active link schemes', () => {
		const maybeLink = parseElement(
			getMaybeLink( {
				linkUrl: 'javascript:alert(1)',
				class: 'profile',
				children: 'Profile',
			} )
		);

		expect( maybeLink.tagName ).toBe( 'DIV' );
		expect( getLink( { linkUrl: 'javascript:alert(1)', text: 'Profile' } ) ).toBe( '' );
	} );

	it( 'escapes generated attributes while preserving valid URLs and bundled images', () => {
		const link = parseElement(
			getLink( {
				linkUrl: 'https://example.com/profile?a=1&b=2',
				className: 'profile" onfocus="alert(1)',
				text: 'View <profile>',
			} )
		);
		const image = parseElement(
			getImage( {
				imageUrl: 'data:image/svg+xml;base64,PHN2Zz4=',
				imageWidth: 72,
				imageHeight: 72,
				imageAlt: 'Avatar',
			} )
		).querySelector( 'img' );

		expect( link.getAttribute( 'href' ) ).toBe( 'https://example.com/profile?a=1&b=2' );
		expect( link.hasAttribute( 'onfocus' ) ).toBe( false );
		expect( link.textContent?.trim() ).toBe( 'View <profile>' );
		expect( image?.getAttribute( 'src' ) ).toBe( 'data:image/svg+xml;base64,PHN2Zz4=' );
	} );
} );
