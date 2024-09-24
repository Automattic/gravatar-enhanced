/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { sha256 } from 'js-sha256';

interface Response {
	data?: GravatarAPIProfile;
	error?: string;
}

const BASE_API_URL = 'https://api.gravatar.com/v3/profiles';

export default async function fetchProfile( email: string ): Promise< Response > {
	if ( ! email || ! validateEmail( email ) ) {
		return { error: __( 'Invalid email address.', 'gravatar-enhanced' ) };
	}

	try {
		const hashedEmail = sha256( email.trim().toLowerCase() );
		const res = await fetch( `${ BASE_API_URL }/${ hashedEmail }?source=gravatar-block` );

		if ( res.status !== 200 ) {
			throw res.status;
		}

		const data = await res.json();

		return { data: { ...data, profile_url: `${ data.profile_url }?utm_source=gravatar-block` } };
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

function validateEmail( email: string ) {
	return /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test( email );
}
