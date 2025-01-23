import type { ParagraphAttrs } from '../shared-types';
import clsx from 'clsx';
import { getMaybeLink } from '.';

type Props = ParagraphAttrs;

export default function getParagraph( { linkUrl, text, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block-paragraph', className ),
		children: `<p class="gravatar-block-paragraph__text">${ text }</p>`,
	} );
}
