import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getDisplayName(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): string | null {
	if ( ! profileData.display_name ) {
		return null;
	}

	return getViewElement( BlockNames.NAME, KnownElemNames.DISPLAY_NAME, deletedElements, {
		className: 'gravatar-text-truncate-2-lines',
		text: profileData.display_name,
	} );
}
