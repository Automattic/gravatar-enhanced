import type { ViewTemplateFn } from '../shared-types';
import { BlockNames, KnownElemNames } from '../shared-types';
import {
	getAvatar,
	getDisplayName,
	getJobTitle,
	getCompany,
	getLocation,
	getDescription,
	getVerifiedAccounts,
	getViewProfile,
} from './elements';
import { getViewElement } from '../utils';

const getDefaultTemplate: ViewTemplateFn = ( profileData, deletedElements ) => `
	${ getViewElement(
		BlockNames.GROUP,
		KnownElemNames.HEADER,
		deletedElements,
		{ className: 'gravatar-block-group--header gravatar-block-group--align-center' },
		[
			getAvatar( profileData, deletedElements, 72, 72 ),
			getViewElement(
				BlockNames.GROUP,
				KnownElemNames.JOB_COMPANY_LOCATION_WRAPPER,
				deletedElements,
				{ linkUrl: profileData.profile_url, verticalAlignment: true },
				[
					getDisplayName( profileData, deletedElements ),
					getViewElement(
						BlockNames.GROUP,
						KnownElemNames.JOB_COMPANY_WRAPPER,
						deletedElements,
						{ className: 'gravatar-block-group--comma-separated' },
						[ getJobTitle( profileData, deletedElements ), getCompany( profileData, deletedElements ) ]
					),
					getLocation( profileData, deletedElements ),
				]
			),
		]
	) }
	${ getDescription( profileData, deletedElements ) }
	${ getViewElement(
		BlockNames.GROUP,
		KnownElemNames.FOOTER,
		deletedElements,
		{ className: 'gravatar-block-group--footer gravatar-block-group--align-center' },
		[ ...getVerifiedAccounts( profileData, deletedElements ), getViewProfile( profileData, deletedElements ) ]
	) }
`;

export default getDefaultTemplate;
