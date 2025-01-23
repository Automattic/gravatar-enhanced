import type { InnerBlockTemplate } from '@wordpress/blocks';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';
import { __ } from '@wordpress/i18n';

export default function getViewProfile(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): InnerBlockTemplate | null {
	if ( ! profileData.profile_url ) {
		return null;
	}

	return getBlockTemplate( BlockNames.LINK, KnownElemNames.VIEW_PROFILE, deletedElements, {
		className: 'gravatar-block-link--align-right',
		linkUrl: profileData.profile_url,
		text: __( 'View profile →', 'gravatar-enhanced' ),
	} );
}
