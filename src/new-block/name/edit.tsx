/**
 * External dependencies
 */
import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import { MaybeLink } from '../components';

export interface Attrs {
	linkUrl?: string;
	text: string;
	className?: string;
	color?: string;
}

export default function Edit( { attributes }: BlockEditProps< Attrs > ) {
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
