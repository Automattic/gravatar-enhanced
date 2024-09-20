import type { BlockEditProps, TemplateArray } from '@wordpress/blocks'; 
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import clsx from 'clsx';

import { MaybeLink } from '../components';

type Props = BlockEditProps< {
	linkUrl: string;
	verticalAlignment: boolean;
	className: string;
	color: string;
} >;

export default function Edit( { attributes, clientId }: Props ) {
	const { linkUrl, verticalAlignment, className, color } = attributes;

	const blockProps = useBlockProps();

	const template: TemplateArray = useSelect(
		( select ) => {
			const { getBlock } = select( 'core/block-editor' );
			// @ts-ignore
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
