import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

type Options = Partial< {
	className: string;
	linkToProfile: boolean;
} >;

export default function getLocation(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: Options = {}
): string | null {
	if ( ! profileData.location ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.LOCATION, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--location gravatar-text-truncate-1-line', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.location,
	} );
}
