import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import { NameIcon } from '../../components';
import metadata from './block.json';

const blockConfig: BlockConfiguration = {
	...metadata,
	icon: NameIcon,
	title: __( 'Name', 'gravatar-enhanced' ),
	description: __( 'The Name block for the Gravatar block.', 'gravatar-enhanced' ),
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
