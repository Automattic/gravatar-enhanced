import clsx from 'clsx';
import type { ImageAttrs } from '../shared-types';
import { getMaybeLink } from '.';
import { escapeAttribute } from '@wordpress/escape-html';

type Props = ImageAttrs;

export default function getImage( {
	linkUrl,
	imageUrl,
	serviceIcon,
	imageWidth,
	imageHeight,
	imageAlt,
	className,
}: Props ): string {
	return getMaybeLink( {
		linkUrl,
		class: clsx(
			'gravatar-block__child',
			'gravatar-block-image',
			serviceIcon && 'gravatar-block-image--account',
			className
		),
		// Only set the extra props for account icons. The image is hidden under a custom text
		// color, so the wrapper carries both the CSS var used as its mask and the accessible
		// name. The URL goes inside url() unquoted so the value can't break out of the attribute.
		...( serviceIcon && {
			style: `--gravatar-icon-url: url(${ serviceIcon })`,
			'aria-label': escapeAttribute( imageAlt ),
		} ),
		children: `<img class="gravatar-block-image__image" src="${ imageUrl }" width="${ imageWidth }" height="${ imageHeight }" alt="${ escapeAttribute(
			imageAlt
		) }"/>`,
	} );
}
