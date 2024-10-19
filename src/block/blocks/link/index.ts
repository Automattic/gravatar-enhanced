import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import { LinkIcon } from '../../components';
import metadata from './block.json';

const blockConfig: BlockConfiguration = {
	...metadata,
	icon: LinkIcon,
	title: __( 'Link', 'gravatar-enhanced' ),
	description: __( 'The Link block for the Gravatar block.', 'gravatar-enhanced' ),
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
