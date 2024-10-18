import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

interface Response {
	data?: GravatarAPIProfile;
	error?: string;
}

const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';

export default async function fetchProfile( hashedEmail: string ): Promise< Response > {
	try {
		const source = 'gravatar-block';
		const url = addQueryArgs( `${ BASE_API_URL }/${ hashedEmail }`, { source } );
		const res = await fetch( url );

		if ( res.status !== 200 ) {
			throw res.status;
		}

		const data = await res.json();
		const profileUrl = addQueryArgs( data.profile_url, { utm_source: source } );

		return { data: { ...data, profile_url: profileUrl } };
	} catch ( code ) {
		let message = __( 'Sorry, we are unable to load this Gravatar profile.', 'gravatar-enhanced' );

		switch ( code ) {
			case 404:
				message = __( 'Profile not found.', 'gravatar-enhanced' );
				break;
			case 429:
				message = __( 'Too many requests.', 'gravatar-enhanced' );
				break;
			case 500:
				message = __( 'Internal server error.', 'gravatar-enhanced' );
				break;
		}

		return { error: message };
	}
}
