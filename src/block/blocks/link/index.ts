import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { KnownElemNames } from '../../edit';
import Edit from './edit';
import { LinkIcon } from '../../components';
import metadata from './block.json';

const title = __( 'Link', 'gravatar-enhanced' );

const blockConfig: BlockConfiguration = {
	...metadata,
	icon: LinkIcon,
	// @ts-ignore
	__experimentalLabel: ( { name } ) => {
		return name === KnownElemNames.VIEW_PROFILE ? __( 'Profile Link', 'gravatar-enhanced' ) : title;
	},
	title,
	description: __( 'The Link block for the Gravatar block.', 'gravatar-enhanced' ),
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
