import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getViewElement } from '../../utils';

export default function getJobTitle(
	profileData: GravatarAPIProfile,
	deletedElements: MainEditAttrs[ 'deletedElements' ]
): string | null {
	if ( ! profileData.job_title ) {
		return null;
	}

	return getViewElement( BlockNames.PARAGRAPH, KnownElemNames.JOB, deletedElements, {
		className: 'gravatar-block-paragraph--job',
		text: profileData.job_title,
	} );
}
