import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { KnownElemNames } from '../../edit';
import Edit from './edit';
import { ImageIcon } from '../../components';
import metadata from './block.json';

const title = __( 'Image', 'gravatar-enhanced' );

const blockConfig: BlockConfiguration = {
	...metadata,
	icon: ImageIcon,
	// @ts-ignore
	__experimentalLabel: ( { name } ) => {
		if ( ! name ) {
			return title;
		}

		return name === KnownElemNames.AVATAR ? __( 'Avatar', 'gravatar-enhanced' ) : name;
	},
	title,
	description: __( 'The Image block for the Gravatar block.', 'gravatar-enhanced' ),
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
