/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import type { Attrs } from '../blocks/column/edit';
import { getMaybeLink } from '.';

export type Props = Attrs;

export default function getColumn(
	{ linkUrl, verticalAlignment, className, color }: Props,
	children: string[]
): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx(
			'gravatar-block-column',
			{ 'gravatar-block-column--vertical-alignment': verticalAlignment },
			className
		),
		style: `color: ${ color };`,
		children,
	} );
}
