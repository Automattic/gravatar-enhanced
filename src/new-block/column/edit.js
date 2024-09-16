import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import classNames from 'classnames';

import { MaybeLink } from '../components';

export default function Edit( { clientId, attributes } ) {
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
			className={ classNames(
				'gravatar-block-column',
				{ 'gravatar-block-column--vertical-alignment': verticalAlignment },
				blockProps.className,
				className
			) }
			style={ { color } }
			linkUrl={ linkUrl }
		>
			<InnerBlocks allowedBlocks={ [] } template={ [ [ 'gravatar/block-column', {}, template ] ] } />
		</MaybeLink>
	);
}
