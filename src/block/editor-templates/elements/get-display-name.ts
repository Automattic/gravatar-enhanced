import type { InnerBlockTemplate } from '@wordpress/blocks';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getDisplayName(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): InnerBlockTemplate | null {
	if ( ! profileData.display_name ) {
		return null;
	}

	return getBlockTemplate( BlockNames.NAME, KnownElemNames.DISPLAY_NAME, deletedElements, {
		className: 'gravatar-text-truncate-2-lines',
		text: profileData.display_name,
	} );
}
