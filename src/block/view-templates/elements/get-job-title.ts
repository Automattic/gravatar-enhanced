import clsx from 'clsx';
import type { MainEditAttrs, ElemOptions } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getJobTitle(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: ElemOptions = {}
): string | null {
	if ( ! profileData.job_title ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.JOB, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--job', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.job_title,
	} );
}
