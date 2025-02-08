import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

type Options = Partial< {
	className: string;
	linkToProfile: boolean;
} >;

export default function getCompany(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: Options = {}
): InnerBlockTemplate | null {
	if ( ! profileData.company ) {
		return null;
	}

	return getBlockTemplate( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--company', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.company,
	} );
}
