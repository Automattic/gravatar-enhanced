import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getDisplayName(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): string | null {
	if ( ! profileData.display_name ) {
		return null;
	}

	return getViewElement( BlockNames.NAME, KnownElemNames.DISPLAY_NAME, deletedElements, {
		className: clsx( 'gravatar-text-truncate-2-lines', className ),
		text: profileData.display_name,
	} );
}
