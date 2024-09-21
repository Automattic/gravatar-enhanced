import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import metadata from './block.json';

const blockConfig: BlockConfiguration = {
	...metadata,
	title: __( 'Column', metadata.textdomain ),
	description: __( 'The Column block of the Gravatar Card.', metadata.textdomain ),
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
