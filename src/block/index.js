import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';
import './style.scss';
import icon_file from './icon.svg';

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	icon: {
		src: () => <img src={ icon_file } width="24" height="24" />,
	},
} );
