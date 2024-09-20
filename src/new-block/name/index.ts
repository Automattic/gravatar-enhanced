import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	...metadata,
	title: __( 'Name', 'gravatar-enhanced' ),
	description: __( 'The Name block of the Gravatar Card.', 'gravatar-enhanced' ),
	edit: Edit,
} );
