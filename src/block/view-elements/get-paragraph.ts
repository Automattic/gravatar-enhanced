import clsx from 'clsx';
import type { ParagraphAttrs } from '../shared-types';
import { getMaybeLink } from '.';
import { escapeHTML } from '@wordpress/escape-html';

type Props = ParagraphAttrs;

export default function getParagraph( { linkUrl, text, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block__child', 'gravatar-block-paragraph', className ),
		children: `<p class="gravatar-block-paragraph__text">${ escapeHTML( text ) }</p>`,
	} );
}
