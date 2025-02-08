import type { MainEditAttrs } from '../../shared-types';
import { BlockNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getVerifiedAccounts(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): string[] {
	const verifiedAccounts = [
		{
			url: profileData.profile_url,
			service_type: 'gravatar',
			service_icon: 'https://secure.gravatar.com/icons/gravatar.svg',
			service_label: 'Gravatar',
			is_hidden: false,
		},
		...( profileData.verified_accounts || [] ),
	];

	return verifiedAccounts.map( ( account ) => {
		if ( account.is_hidden ) {
			return null;
		}

		return getViewElement( BlockNames.IMAGE, account.service_type, deletedElements, {
			linkUrl: account.url,
			imageUrl: account.service_icon,
			imageWidth: 32,
			imageHeight: 32,
			imageAlt: account.service_label,
		} );
	} );
}
