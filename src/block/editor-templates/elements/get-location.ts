import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getLocation(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): InnerBlockTemplate | null {
	if ( ! profileData.location ) {
		return null;
	}

	return getBlockTemplate( BlockNames.PARAGRAPH, KnownElemNames.LOCATION, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--location gravatar-text-truncate-1-line', className ),
		text: profileData.location,
	} );
}
