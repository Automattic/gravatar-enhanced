import clsx from 'clsx';
import type { Attrs } from '../blocks/name/edit';
import { getMaybeLink } from '.';

export type Props = Attrs;

export default function getName( { linkUrl, text, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block-name', className ),
		children: `<h4 class="gravatar-block-name__text">${ text }</h4>`,
	} );
}
