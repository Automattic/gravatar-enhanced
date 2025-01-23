import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getCompany(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): string | null {
	if ( ! profileData.company ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, deletedElements, {
		className: 'gravatar-block-paragraph--company',
		text: profileData.company,
	} );
}
