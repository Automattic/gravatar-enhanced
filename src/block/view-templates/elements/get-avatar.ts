import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getAvatar(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	imageWidth = 72,
	imageHeight = 72
): string | null {
	if ( ! profileData.avatar_url ) {
		return null;
	}

	return getViewElement( BlockNames.IMAGE, KnownElemNames.AVATAR, deletedElements, {
		className: 'gravatar-block-image--avatar',
		linkUrl: profileData.profile_url,
		imageUrl: profileData.avatar_url,
		imageWidth,
		imageHeight,
		imageAlt: profileData.avatar_alt_text || profileData.display_name,
	} );
}
