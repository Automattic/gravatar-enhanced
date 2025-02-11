import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';
import type { ParagraphAttrs } from '../../shared-types';
import { MaybeLink } from '../../components';

type Props = BlockEditProps< ParagraphAttrs >;

export default function Edit( { attributes }: Props ) {
	const { linkUrl, text, className } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ clsx( 'gravatar-block', 'gravatar-block-paragraph', blockProps.className, className ) }
			linkUrl={ linkUrl }
		>
			<p className="gravatar-block-paragraph__text">{ text }</p>
		</MaybeLink>
	);
}
