import clsx from 'clsx';
import type { LinkAttrs } from '../shared-types';
import { escapeAttribute, escapeHTML } from '@wordpress/escape-html';
import getSafeUrlAttribute from './get-safe-url-attribute';

type Props = LinkAttrs;

export default function getLink( { linkUrl, text, className }: Props ): string {
	const safeLinkUrl = getSafeUrlAttribute( linkUrl, 'link' );

	if ( ! safeLinkUrl ) {
		return '';
	}

	return `
		<a
			class="${ escapeAttribute( clsx( 'gravatar-block__child', 'gravatar-block-link', className ) ) }"
			href="${ safeLinkUrl }"
			target="_blank"
			rel="noopener noreferrer"
		>
			${ escapeHTML( text ) }
		</a>
	`;
}
