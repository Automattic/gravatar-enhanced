import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';
import { __ } from '@wordpress/i18n';

export default function getViewProfile(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): InnerBlockTemplate | null {
	if ( ! profileData.profile_url ) {
		return null;
	}

	return getBlockTemplate( BlockNames.LINK, KnownElemNames.VIEW_PROFILE, deletedElements, {
		className: clsx( 'gravatar-block-link--align-right', className ),
		linkUrl: profileData.profile_url,
		text: __( 'View profile →', 'gravatar-enhanced' ),
	} );
}
