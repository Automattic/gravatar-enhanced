/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import type { Attrs } from '../blocks/image/edit';
import { getMaybeLink } from '.';

export type Props = Attrs;

export default function getImage( { linkUrl, imageUrl, imageWidth, imageHeight, imageAlt, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block-image', className ),
		children: `<img class="gravatar-block-image__image" src="${ imageUrl }" width="${ imageWidth }" height="${ imageHeight }" alt="${ imageAlt }"/>`,
	} );
}
