import { BlockEditProps } from '@wordpress/blocks'; 
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import clsx from 'clsx';

import { MaybeLink } from '../components';

type Props = BlockEditProps< Partial< {
	linkUrl: string;
	verticalAlignment: boolean;
	className: string;
	color: string;
} > >;

export default function Edit( { attributes, clientId }: Props ) {
	const { linkUrl, verticalAlignment, className, color } = attributes;

	const blockProps = useBlockProps();

	const template = useSelect(
		( select ) => {
			const { getBlock } = select( 'core/block-editor' );
			const blocks = getBlock( clientId )?.innerBlocks || [];

			return blocks.map( ( { name, attrs } ) => [ name, attrs ] );
		},
		[ clientId ]
	);

	return (
		<MaybeLink
			{ ...blockProps }
			className={ clsx(
				'gravatar-block-column',
				{ 'gravatar-block-column--vertical-alignment': verticalAlignment },
				blockProps.className,
				className
			) }
			style={ { color } }
			linkUrl={ linkUrl }
		>
			<InnerBlocks allowedBlocks={ [] } template={ template } />
		</MaybeLink>
	);
}
