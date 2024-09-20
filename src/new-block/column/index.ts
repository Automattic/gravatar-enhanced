import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	...metadata,
	title: __( 'Column', 'gravatar-enhanced' ),
	description: __( 'The Column block of the Gravatar Card.', 'gravatar-enhanced' ),
	edit: Edit,
} );
