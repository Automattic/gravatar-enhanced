import { useBlockProps } from '@wordpress/block-editor';
import classNames from 'classnames';

import { MaybeLink } from '../components';

export default function Edit( { attributes } ) {
	const { linkUrl, text, className, color } = attributes;

	const blockProps = useBlockProps();

	return (
		<MaybeLink
			{ ...blockProps }
			className={ classNames( 'gravatar-block-paragraph', blockProps.className, className ) }
			linkUrl={ linkUrl }
		>
			<p className="gravatar-block-paragraph__text" style={ { color } }>
				{ text }
			</p>
		</MaybeLink>
	);
}
