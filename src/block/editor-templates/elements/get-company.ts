import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

export default function getCompany(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): InnerBlockTemplate | null {
	if ( ! profileData.company ) {
		return null;
	}

	return getBlockTemplate( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--company', className ),
		text: profileData.company,
	} );
}
