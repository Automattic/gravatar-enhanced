import { ProfileData } from '@gravatar-com/hovercards';

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
		/* eslint-disable camelcase */
		verifiedAccounts: verifiedAccounts.map( ( { url, service_label, service_icon, service_type, is_hidden } ) => ( {
			url,
			label: service_label,
			icon: service_icon,
			type: service_type,
			isHidden: is_hidden,
		} ) ),
		/* eslint-enable camelcase */
	};
}
