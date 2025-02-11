import clsx from 'clsx';
import type { LinkAttrs } from '../shared-types';

type Props = LinkAttrs;

export default function getLink( { linkUrl, text, className }: Props ): string {
	return `
		<a
			class="${ clsx( 'gravatar-block__child', 'gravatar-block-link', className ) }"
			href="${ linkUrl }"
			target="_blank"
		>
			${ text }
		</a>
	`;
}
