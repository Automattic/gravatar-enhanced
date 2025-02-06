import clsx from 'clsx';
import type { MainEditAttrs, ElemOptions } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getDescription(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: ElemOptions = {}
): string | null {
	if ( ! profileData.description ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.DESCRIPTION, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--description gravatar-text-truncate-2-lines', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.description,
	} );
}
