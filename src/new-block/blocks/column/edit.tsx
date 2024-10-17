import type { BlockEditProps, TemplateArray } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import clsx from 'clsx';
import { MaybeLink } from '../../components';

export type Attrs = Partial< {
	linkUrl: string;
	verticalAlignment: boolean;
	className: string;
} >;

export default function Edit( { attributes, clientId }: BlockEditProps< Attrs > ) {
	const { linkUrl, verticalAlignment, className } = attributes;

	const blockProps = useBlockProps();

	const template: TemplateArray = useSelect(
		( select: SelectFn ) => {
			const { innerBlocks = [] } = select( 'core/block-editor' ).getBlock( clientId );

			return innerBlocks.map( ( { name, attrs } ) => [ name, attrs ] );
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
			linkUrl={ linkUrl }
		>
			<InnerBlocks allowedBlocks={ [] } template={ template } />
		</MaybeLink>
	);
}
