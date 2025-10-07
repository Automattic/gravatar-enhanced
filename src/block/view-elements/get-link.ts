import clsx from 'clsx';
import type { LinkAttrs } from '../shared-types';
import { escapeHTML } from '@wordpress/escape-html';

type Props = LinkAttrs;

export default function getLink( { linkUrl, text, className }: Props ): string {
	return `
		<a
			class="${ clsx( 'gravatar-block__child', 'gravatar-block-link', className ) }"
			href="${ linkUrl }"
			target="_blank"
		>
			${ escapeHTML( text ) }
		</a>
	`;
}
