import type { InnerBlockTemplate } from '@wordpress/blocks';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getAvatar(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	imageWidth = 72,
	imageHeight = 72
): InnerBlockTemplate | null {
	if ( ! profileData.avatar_url ) {
		return null;
	}

	return getBlockTemplate( BlockNames.IMAGE, KnownElemNames.AVATAR, deletedElements, {
		className: 'gravatar-block-image--avatar',
		linkUrl: profileData.profile_url,
		imageUrl: profileData.avatar_url,
		imageWidth,
		imageHeight,
		imageAlt: profileData.avatar_alt_text || profileData.display_name,
	} );
}
