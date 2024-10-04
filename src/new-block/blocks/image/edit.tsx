/**
 * External dependencies
 */
import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import { MaybeLink } from '../../components';

export interface Attrs {
	linkUrl?: string;
	imageUrl: string;
	imageWidth: number;
	imageHeight: number;
	imageAlt: string;
	className?: string;
}

export default function Edit( { attributes }: BlockEditProps< Attrs > ) {
	const { linkUrl, imageUrl, imageWidth, imageHeight, imageAlt, className } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ clsx( 'gravatar-block-image', blockProps.className, className ) }
			linkUrl={ linkUrl }
		>
			<img
				className="gravatar-block-image__image"
				src={ imageUrl }
				width={ imageWidth }
				height={ imageHeight }
				alt={ imageAlt }
			/>
		</MaybeLink>
	);
}
