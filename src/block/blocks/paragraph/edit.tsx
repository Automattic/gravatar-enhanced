import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';
import { MaybeLink } from '../../components';

export interface Attrs {
	linkUrl?: string;
	text: string;
	className?: string;
}

export default function Edit( { attributes }: BlockEditProps< Attrs > ) {
	const { linkUrl, text, className } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ clsx( 'gravatar-block-paragraph', blockProps.className, className ) }
			linkUrl={ linkUrl }
		>
			<p className="gravatar-block-paragraph__text">{ text }</p>
		</MaybeLink>
	);
}
