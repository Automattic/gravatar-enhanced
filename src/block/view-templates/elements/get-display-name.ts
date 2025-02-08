import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

type Options = Partial< {
	className: string;
	linkToProfile: boolean;
} >;

export default function getDisplayName(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: Options = {}
): string | null {
	if ( ! profileData.display_name ) {
		return null;
	}

	return getViewElement( BlockNames.NAME, KnownElemNames.DISPLAY_NAME, deletedElements, {
		className: clsx( 'gravatar-text-truncate-2-lines', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.display_name,
	} );
}
