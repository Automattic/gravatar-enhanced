import clsx from 'clsx';
import type { ColumnAttrs } from '../shared-types';
import { getMaybeLink } from '.';

type Props = ColumnAttrs;

export default function getColumn( { linkUrl, verticalAlignment, className }: Props, children: string[] ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx(
			'gravatar-block-column',
			{ 'gravatar-block-column--vertical-alignment': verticalAlignment },
			className
		),
		children,
	} );
}
