/**
 * External dependencies
 */
import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';

export interface Attrs {
	linkUrl: string;
	text: string;
	className?: string;
}

export default function Edit( { attributes }: BlockEditProps< Attrs > ) {
	const { linkUrl, text, className } = attributes;

	const blockProps = useBlockProps();

	return (
		<a
			{ ...blockProps }
			className={ clsx( 'gravatar-block-link', blockProps.className, className ) }
			href={ linkUrl }
			target="_blank"
			rel="noreferrer"
		>
			<span>{ text }</span>
			<span>&#8594;</span>
		</a>
	);
}
