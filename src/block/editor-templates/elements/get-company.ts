import type { InnerBlockTemplate } from '@wordpress/blocks';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getCompany(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): InnerBlockTemplate | null {
	if ( ! profileData.company ) {
		return null;
	}

	return getBlockTemplate( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, deletedElements, {
		className: 'gravatar-block-paragraph--company',
		text: profileData.company,
	} );
}
