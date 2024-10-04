/**
 * External dependencies
 */
import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';

const blockConfig: BlockConfiguration = {
	...metadata,
	title: __( 'Image', 'gravatar-enhanced' ),
	description: __( 'The Image block of the Gravatar Card.', 'gravatar-enhanced' ),
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
