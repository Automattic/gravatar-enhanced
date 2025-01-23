import type { InnerBlockTemplate } from '@wordpress/blocks';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getDescription(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): InnerBlockTemplate | null {
	if ( ! profileData.description ) {
		return null;
	}

	return getBlockTemplate( BlockNames.PARAGRAPH, KnownElemNames.DESCRIPTION, deletedElements, {
		className: 'gravatar-text-truncate-2-lines',
		text: profileData.description,
	} );
}
