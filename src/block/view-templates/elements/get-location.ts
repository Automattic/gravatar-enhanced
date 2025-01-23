import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getLocation(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): string | null {
	if ( ! profileData.location ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.LOCATION, deletedElements, {
		className: 'gravatar-block-paragraph--location gravatar-text-truncate-1-line',
		text: profileData.location,
	} );
}
