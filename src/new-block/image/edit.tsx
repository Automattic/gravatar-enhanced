import type { BlockEditProps } from '@wordpress/blocks'; 
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';

import { MaybeLink } from '../components';

interface BlockAttrs {
	linkUrl: string;
	imageUrl: string;
	imageWidth: number;
	imageHeight: number;
	alt: string;
	className: string;
}

export default function Edit( { attributes }: BlockEditProps< BlockAttrs > ) {
	const { linkUrl, imageUrl, imageWidth, imageHeight, alt, className } = attributes;

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
				alt={ alt }
			/>
		</MaybeLink>
	);
}
