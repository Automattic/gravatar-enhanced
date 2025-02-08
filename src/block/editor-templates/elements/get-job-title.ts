import type { InnerBlockTemplate } from '@wordpress/blocks';
import clsx from 'clsx';
import type { MainEditAttrs } from '../../shared-types';
import { BlockNames, KnownElemNames } from '../../shared-types';
import { getBlockTemplate } from '../../utils';

type Options = Partial< {
	className: string;
	linkToProfile: boolean;
} >;

export default function getJobTitle(
	profileData: Partial< GravatarAPIProfile >,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	options: Options = {}
): InnerBlockTemplate | null {
	if ( ! profileData.job_title ) {
		return null;
	}

	return getBlockTemplate( BlockNames.PARAGRAPH, KnownElemNames.JOB, deletedElements, {
		className: clsx( 'gravatar-block-paragraph--job', options.className ),
		linkUrl: options.linkToProfile && profileData.profile_url,
		text: profileData.job_title,
	} );
}
