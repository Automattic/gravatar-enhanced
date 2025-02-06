import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs, ElemOptions } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getDisplayName(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: ElemOptions = {}
): InnerBlockTemplate | null {
	if ( ! profileData.display_name ) {
		return null;
	}

	return getBlockTemplate( BlockNames.NAME, KnownElemNames.DISPLAY_NAME, deletedElements, {
		className: clsx( 'gravatar-text-truncate-2-lines', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.display_name,
	} );
}
