/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import type { Attrs } from '../blocks/link/edit';

export type Props = Attrs;

export default function getLink( { linkUrl, text, className }: Props ): string {
	return `
		<a
			class="${ clsx( 'gravatar-block-link', className ) }"
			href="${ linkUrl }"
			target="_blank"
			rel="noreferrer"
		>
			<span>${ text }</span>
			<span>&#8594;</span>
		</a>
	`;
}
