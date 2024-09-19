import { __ } from '@wordpress/i18n';
import { Hovercards, ProfileData } from '@gravatar-com/hovercards';

// We need our own avatarUrl as the profile one will refer to the primary account address, not necessarily the one used on this site
export function convertJsonToUser( profile: GravatarAPIProfile, avatarUrl: string ): ProfileData {
	// Bit of a nuisance that we have to convert like this
	const {
		hash,
		display_name: displayName,
		description,
		profile_url: profileUrl,
		company,
		location,
		job_title: jobTitle,
		verified_accounts: verifiedAccounts,
	} = profile;

	return {
		hash,
		displayName,
		description,
		avatarUrl,
		profileUrl,
		company,
		location,
		jobTitle,
		verifiedAccounts: verifiedAccounts.map(
			( { url, service_label, service_icon, service_type, is_hidden } ) => ( {
				url,
				label: service_label,
				icon: service_icon,
				type: service_type,
				isHidden: is_hidden,
			} )
		),
	};
}

export function attachInlineHovercard( element, hash ) {
	var avatar_url = `https://gravatar.com/avatar/${ hash }`;
	// Add loading skeleton
	const skeleton = Hovercards.createHovercardSkeleton();
	element.innerHTML = skeleton.outerHTML;
	// Fetch hovercard data
	fetch( `https://api.gravatar.com/v3/profiles/${ hash }?source=hovercard` )
		.then( ( res ) => {
			if ( res.status !== 200 ) {
				element.innerHTML = Hovercards.createHovercardError(
					avatar_url,
					__( 'Gravata profile information could not be loaded.' )
				).outerHTML;
				throw res.status;
			}

			return res.json();
		} )
		.then( ( data ) => {
			const hovercardData = convertJsonToUser( data, avatar_url );
			element.innerHTML = Hovercards.createHovercard( hovercardData ).outerHTML;
		} );
}
