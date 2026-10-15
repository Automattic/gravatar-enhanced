import clsx from 'clsx';
import type { ImageAttrs } from '../shared-types';
import { getMaybeLink } from '.';
import { escapeAttribute } from '@wordpress/escape-html';
import getSafeUrlAttribute from './get-safe-url-attribute';

type Props = ImageAttrs;

export default function getImage( { linkUrl, imageUrl, imageWidth, imageHeight, imageAlt, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block__child', 'gravatar-block-image', className ),
		children: `<img class="gravatar-block-image__image" src="${ getSafeUrlAttribute(
			imageUrl,
			'image'
		) }" width="${ escapeAttribute( String( imageWidth ) ) }" height="${ escapeAttribute(
			String( imageHeight )
		) }" alt="${ escapeAttribute( imageAlt ) }"/>`,
	} );
}
