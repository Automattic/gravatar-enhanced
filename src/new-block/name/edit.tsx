import type { BlockEditProps } from '@wordpress/blocks'; 
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';

import { MaybeLink } from '../components';

type Props = BlockEditProps< {
	linkUrl: string;
	text: string;
	className: string;
	color: string;
} >;

export default function Edit( { attributes }: Props ) {
	const { linkUrl, text, className, color } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ clsx( 'gravatar-block-name', blockProps.className, className ) }
			linkUrl={ linkUrl }
		>
			<h4 className="gravatar-block-name__text" style={ { color } }>
				{ text }
			</h4>
		</MaybeLink>
	);
}
