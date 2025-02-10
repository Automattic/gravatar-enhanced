import type { BlockEditProps } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';
import type { NameAttrs } from '../../shared-types';
import { MaybeLink } from '../../components';

type Props = BlockEditProps< NameAttrs >;

export default function Edit( { attributes }: Props ) {
	const { linkUrl, text, className } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ clsx( 'gravatar-block-name', blockProps.className, className ) }
			linkUrl={ linkUrl }
		>
			<h4 className="gravatar-block-name__text">{ text }</h4>
		</MaybeLink>
	);
}
