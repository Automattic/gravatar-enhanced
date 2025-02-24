import type { EditTemplateFn } from '../shared-types';
import { BlockNames, KnownElemNames } from '../shared-types';
import { getAvatar, getDisplayName, getJobTitle, getLocation, getDescription, getVerifiedAccounts } from './elements';
import { getBlockTemplate } from '../utils';

const getPortraitTemplate: EditTemplateFn = ( profileData, deletedElements ) =>
	[
		getAvatar( profileData, deletedElements, 354, 354 ),
		getDisplayName( profileData, deletedElements, { linkToProfile: true } ),
		getJobTitle( profileData, deletedElements, { linkToProfile: true } ),
		getLocation( profileData, deletedElements, { linkToProfile: true } ),
		getDescription( profileData, deletedElements, { className: 'gravatar-text-truncate-3-lines' } ),
		getBlockTemplate(
			BlockNames.GROUP,
			KnownElemNames.VERIFIED_ACCOUNTS,
			deletedElements,
			{ className: 'gravatar-block-group--verified-accounts' },
			getVerifiedAccounts( profileData, deletedElements )
		),
	].filter( Boolean );

export default getPortraitTemplate;
