/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import type { Attrs } from '../paragraph/edit';
import { getMaybeLink } from '.';

export type Props = Attrs;

export default function getParagraph( { linkUrl, text, className, color }: Props ): string {
	const style = color ? `style="color: ${ color };"` : '';

	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block-paragraph', className ),
		children: `<p class="gravatar-block-paragraph__text" ${ style }>${ text }</p>`,
	} );
}
