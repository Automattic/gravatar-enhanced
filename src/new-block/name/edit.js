import { useBlockProps } from '@wordpress/block-editor';
import classNames from 'classnames';

import { MaybeLink } from '../components';

export default function Edit( { attributes } ) {
	const { linkUrl, text, className, color } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ classNames( 'gravatar-block-name', blockProps.className, className ) }
			linkUrl={ linkUrl }
		>
			<h4 className="gravatar-block-name__text" style={ { color } }>
				{ text }
			</h4>
		</MaybeLink>
	);
}
