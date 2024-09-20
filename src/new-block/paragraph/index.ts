import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	...metadata,
	title: __( 'Paragraph', 'gravatar-enhanced' ),
	description: __( 'The Paragraph block of the Gravatar Card.', 'gravatar-enhanced' ),
	edit: Edit,
} );
