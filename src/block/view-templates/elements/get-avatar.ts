import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';
// TODO: Fix this TS error...
import avatarPlaceholder from '../../images/avatar-placeholder.svg';

type Options = Partial< {
	className: string;
	linkToProfile: boolean;
} >;

export default function getAvatar(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	imageWidth,
	imageHeight,
	options: Options = { linkToProfile: true }
): string | null {
	return getViewElement( BlockNames.IMAGE, KnownElemNames.AVATAR, deletedElements, {
		className: clsx( 'gravatar-block-image--avatar', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		imageUrl: profileData.avatar_url || avatarPlaceholder,
		imageWidth,
		imageHeight,
		imageAlt: profileData.avatar_alt_text || profileData.display_name,
	} );
}
