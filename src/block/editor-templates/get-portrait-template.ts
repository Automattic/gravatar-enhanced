import type { InnerBlockTemplate } from '@wordpress/blocks';
import type { MainEditAttrs } from '../shared-types';
import { BlockNames, KnownElemNames } from '../shared-types';
import { getAvatar, getDisplayName, getJobTitle, getLocation, getDescription, getVerifiedAccounts } from './elements';
import { getBlockTemplate } from '../utils';

export default function getPortraitTemplate(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): InnerBlockTemplate[] {
	return [
		getAvatar( profileData, deletedElements, 354, 354 ),
		getDisplayName( profileData, deletedElements ),
		getJobTitle( profileData, deletedElements ),
		getLocation( profileData, deletedElements ),
		getDescription( profileData, deletedElements, 'gravatar-text-truncate-3-lines' ),
		getBlockTemplate(
			BlockNames.COLUMN,
			KnownElemNames.VERIFIED_ACCOUNTS,
			deletedElements,
			{ className: 'gravatar-block-column--verified-accounts' },
			getVerifiedAccounts( profileData, deletedElements )
		),
	].filter( Boolean );
}
