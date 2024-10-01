/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import type { Attrs } from '../name/edit';
import { getMaybeLink } from '.';

export type Props = Attrs;

export default function getName( { linkUrl, text, className, color }: Props ): string {
	const style = color ? `style="color: ${ color };"` : '';

	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block-name', className ),
		children: `<h4 class="gravatar-block-name__text" ${ style }>${ text }</h4>`,
	} );
}