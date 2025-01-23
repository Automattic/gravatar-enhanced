import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getDescription(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): string | null {
	if ( ! profileData.description ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.DESCRIPTION, deletedElements, {
		className: 'gravatar-text-truncate-2-lines',
		text: profileData.description,
	} );
}
