import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getDescription(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): InnerBlockTemplate | null {
	if ( ! profileData.description ) {
		return null;
	}

	return getBlockTemplate( BlockNames.PARAGRAPH, KnownElemNames.DESCRIPTION, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--description gravatar-text-truncate-2-lines', className ),
		text: profileData.description,
	} );
}
