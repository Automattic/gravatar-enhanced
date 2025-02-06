import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getCompany(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): string | null {
	if ( ! profileData.company ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--company', className ),
		text: profileData.company,
	} );
}
