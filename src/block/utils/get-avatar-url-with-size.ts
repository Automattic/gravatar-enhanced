import { addQueryArgs } from '@wordpress/url';

export default function getAvatarUrlWithSize( avatarUrl: string, size: number ): string {
	// Use the double size of the avatar for better quality on Retina displays.
	return addQueryArgs( avatarUrl, { s: size * 2 } );
}
