import clsx from 'clsx';
import type { ImageAttrs } from '../shared-types';
import { getMaybeLink } from '.';

type Props = ImageAttrs;

export default function getImage( { linkUrl, imageUrl, imageWidth, imageHeight, imageAlt, className }: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx( 'gravatar-block', 'gravatar-block-image', className ),
		children: `<img class="gravatar-block-image__image" src="${ imageUrl }" width="${ imageWidth }" height="${ imageHeight }" alt="${ imageAlt }"/>`,
	} );
}
