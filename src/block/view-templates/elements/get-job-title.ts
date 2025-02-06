import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getJobTitle(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	className?: string
): string | null {
	if ( ! profileData.job_title ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.JOB, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--job', className ),
		text: profileData.job_title,
	} );
}
