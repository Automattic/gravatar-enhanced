import clsx from 'clsx';
import type { MainEditAttrs, ElemOptions } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getAvatar(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	imageWidth,
	imageHeight,
	options: ElemOptions = { linkToProfile: true }
): string | null {
	if ( ! profileData.avatar_url ) {
		return null;
	}

	return getViewElement( BlockNames.IMAGE, KnownElemNames.AVATAR, deletedElements, {
		className: clsx( 'gravatar-block-image--avatar', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		imageUrl: profileData.avatar_url,
		imageWidth,
		imageHeight,
		imageAlt: profileData.avatar_alt_text || profileData.display_name,
	} );
}
