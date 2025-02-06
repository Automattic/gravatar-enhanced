import clsx from 'clsx';
import type { MainEditAttrs, ElemOptions } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getCompany(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: ElemOptions = {}
): string | null {
	if ( ! profileData.company ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--company', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.company,
	} );
}
