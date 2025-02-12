import clsx from 'clsx';
import type { NameAttrs } from '../shared-types';
import { getMaybeLink } from '.';

type Props = NameAttrs;

export default function getName( { linkUrl, text, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block__child', 'gravatar-block-name', className ),
		children: `<h4 class="gravatar-block-name__text">${ text }</h4>`,
	} );
}
