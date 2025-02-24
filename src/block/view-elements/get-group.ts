import clsx from 'clsx';
import type { GroupAttrs } from '../shared-types';
import { getMaybeLink } from '.';

type Props = GroupAttrs;

export default function getGroup( { linkUrl, verticalAlignment, className }: Props, children: string[] ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx(
			'gravatar-block__child',
			'gravatar-block-group',
			{ 'gravatar-block-group--vertical-alignment': verticalAlignment },
			className
		),
		children,
	} );
}
