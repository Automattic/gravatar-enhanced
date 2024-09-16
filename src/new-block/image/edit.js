import { useBlockProps } from '@wordpress/block-editor';
import classNames from 'classnames';

import { MaybeLink } from '../components';

export default function Edit( { attributes } ) {
	const { linkUrl, imageUrl, imageWidth, imageHeight, alt, className } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ classNames( 'gravatar-block-image', blockProps.className, className ) }
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
