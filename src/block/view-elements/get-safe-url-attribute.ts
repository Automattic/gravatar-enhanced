import { escapeAttribute } from '@wordpress/escape-html';

type UrlContext = 'image' | 'link';

const SAFE_PROTOCOLS = new Set( [ 'http:', 'https:' ] );
const DATA_IMAGE_PATTERN = /^data:image\/(?:gif|jpe?g|png|svg\+xml|webp)[;,]/i;

// Validate a URL before serializing it into an HTML attribute. Relative URLs
// are allowed, as are the data-image URLs emitted for bundled image assets.
export default function getSafeUrlAttribute( value: string | undefined, context: UrlContext ): string {
	const url = value?.trim();

	if ( ! url ) {
		return '';
	}

	if ( context === 'image' && DATA_IMAGE_PATTERN.test( url ) ) {
		return escapeAttribute( url );
	}

	try {
		const parsedUrl = new URL( url, 'https://example.invalid' );

		if ( ! SAFE_PROTOCOLS.has( parsedUrl.protocol ) ) {
			return '';
		}
	} catch {
		return '';
	}

	return escapeAttribute( url );
}
