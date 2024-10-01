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
	color?: string;
}

export default function Edit( { attributes }: BlockEditProps< Attrs > ) {
	const { linkUrl, text, className, color } = attributes;

	const blockProps = useBlockProps();

	return (
		<a
			{ ...blockProps }
			className={ clsx( 'gravatar-block-link', blockProps.className, className ) }
			style={ { color } }
			href={ linkUrl }
			target="_blank"
			rel="noreferrer"
		>
			<span>{ text }</span>
			<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
				<path
					stroke={ color }
					d="M12.6667 8.33338L9.16666 12.1667M12.6667 8.33338L2.66666 8.33338M12.6667 8.33338L9.16666 4.83338"
					strokeWidth="1.5"
				></path>
			</svg>
		</a>
	);
}
