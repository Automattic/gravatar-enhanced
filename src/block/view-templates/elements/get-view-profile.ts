import { __ } from '@wordpress/i18n';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getViewProfile(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): string {
	if ( ! profileData.profile_url ) {
		return '';
	}

	return getViewElement( BlockNames.LINK, KnownElemNames.VIEW_PROFILE, deletedElements, {
		className: clsx( 'gravatar-block-link--align-right', className ),
		linkUrl: profileData.profile_url,
		text: __( 'View profile →', 'gravatar-enhanced' ),
	} );
}
