import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';
import type { LinkAttrs } from '../../shared-types';

type Props = BlockEditProps< LinkAttrs >;

export default function Edit( { attributes }: Props ) {
	const { linkUrl, text, className } = attributes;

	const blockProps = useBlockProps();

	return (
		// eslint-disable-next-line react/jsx-no-target-blank
		<a
			{ ...blockProps }
			className={ clsx( 'gravatar-block', 'gravatar-block-link', blockProps.className, className ) }
			href={ linkUrl }
			target="_blank"
		>
			{ text }
		</a>
	);
}
