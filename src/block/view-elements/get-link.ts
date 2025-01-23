import type { LinkAttrs } from '../shared-types';
import clsx from 'clsx';

type Props = LinkAttrs;

export default function getLink( { linkUrl, text, className }: Props ): string {
	return `
		<a
			class="${ clsx( 'gravatar-block-link', className ) }"
			href="${ linkUrl }"
			target="_blank"
		>
			${ text }
		</a>
	`;
}
