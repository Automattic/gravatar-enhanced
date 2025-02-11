import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';
import type { ImageAttrs } from '../../shared-types';
import { MaybeLink } from '../../components';

type Props = BlockEditProps< ImageAttrs >;

export default function Edit( { attributes }: Props ) {
	const { linkUrl, imageUrl, imageWidth, imageHeight, imageAlt, className } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ clsx( 'gravatar-block', 'gravatar-block-image', blockProps.className, className ) }
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
