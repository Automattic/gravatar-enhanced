import clsx from 'clsx';
import type { Attrs } from '../blocks/paragraph/edit';
import { getMaybeLink } from '.';

export type Props = Attrs;

export default function getParagraph( { linkUrl, text, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block-paragraph', className ),
		children: `<p class="gravatar-block-paragraph__text">${ text }</p>`,
	} );
}
