import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getAvatar(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	imageWidth,
	imageHeight,
	className?: string
): InnerBlockTemplate | null {
	if ( ! profileData.avatar_url ) {
		return null;
	}

	return getBlockTemplate( BlockNames.IMAGE, KnownElemNames.AVATAR, deletedElements, {
		className: clsx( 'gravatar-block-image--avatar', className ),
		linkUrl: profileData.profile_url,
		imageUrl: profileData.avatar_url,
		imageWidth,
		imageHeight,
		imageAlt: profileData.avatar_alt_text || profileData.display_name,
	} );
}
