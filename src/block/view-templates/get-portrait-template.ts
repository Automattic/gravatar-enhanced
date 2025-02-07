import type { MainEditAttrs } from '../shared-types';
import { BlockNames, KnownElemNames } from '../shared-types';
import { getAvatar, getDisplayName, getJobTitle, getLocation, getDescription, getVerifiedAccounts } from './elements';
import { getViewElement } from '../utils';

export default function getPortraitTemplate(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): string {
	return `
		${ getAvatar( profileData, deletedElements, 354, 354 ) }
		${ getDisplayName( profileData, deletedElements, { linkToProfile: true } ) }
		${ getJobTitle( profileData, deletedElements, { linkToProfile: true } ) }
		${ getLocation( profileData, deletedElements, { linkToProfile: true } ) }
		${ getDescription( profileData, deletedElements, { className: 'gravatar-text-truncate-3-lines' } ) }
		${ getViewElement(
			BlockNames.COLUMN,
			KnownElemNames.VERIFIED_ACCOUNTS,
			deletedElements,
			{ className: 'gravatar-block-column--verified-accounts' },
			getVerifiedAccounts( profileData, deletedElements )
		) }
	`;
}
