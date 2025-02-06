import type { InnerBlockTemplate } from '@wordpress/blocks';
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
import type { MainEditAttrs } from '../shared-types';
import { BlockNames, KnownElemNames } from '../shared-types';
import { getBlockTemplate } from '../utils';

export default function getDefaultTemplate(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): InnerBlockTemplate[] {
	return [
		getBlockTemplate(
			BlockNames.COLUMN,
			KnownElemNames.HEADER,
			deletedElements,
			{ className: 'gravatar-block-column--header gravatar-block-column--align-center' },
			[
				getAvatar( profileData, deletedElements, 72, 72 ),
				getBlockTemplate(
					BlockNames.COLUMN,
					KnownElemNames.JOB_COMPANY_LOCATION_WRAPPER,
					deletedElements,
					{ linkUrl: profileData.profile_url, verticalAlignment: true },
					[
						getDisplayName( profileData, deletedElements ),
						getBlockTemplate(
							BlockNames.COLUMN,
							KnownElemNames.JOB_COMPANY_WRAPPER,
							deletedElements,
							{ className: 'gravatar-block-column--comma-separated' },
							[ getJobTitle( profileData, deletedElements ), getCompany( profileData, deletedElements ) ]
						),
						getLocation( profileData, deletedElements ),
					]
				),
			]
		),
		getDescription( profileData, deletedElements ),
		getBlockTemplate(
			BlockNames.COLUMN,
			KnownElemNames.FOOTER,
			deletedElements,
			{ className: 'gravatar-block-column--footer gravatar-block-column--align-center' },
			[ ...getVerifiedAccounts( profileData, deletedElements ), getViewProfile( profileData, deletedElements ) ]
		),
	].filter( Boolean );
}
