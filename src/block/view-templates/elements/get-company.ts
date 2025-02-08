import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

type Options = Partial< {
	className: string;
	linkToProfile: boolean;
} >;

export default function getCompany(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: Options = {}
): string {
	if ( ! profileData.company ) {
		return '';
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--company', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.company,
	} );
}
