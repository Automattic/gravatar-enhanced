import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import { GravatarCardIcon } from './components';
import metadata from './block.json';

const blockConfig: BlockConfiguration = {
	...metadata,
	icon: GravatarCardIcon,
	title: __( 'Gravatar Profile', 'gravatar-enhanced' ),
	description: __( 'Display user information directly from Gravatar Public Profiles.', 'gravatar-enhanced' ),
	keywords: [
		__( 'gravatar', 'gravatar-enhanced' ),
		__( 'profile', 'gravatar-enhanced' ),
		__( 'author', 'gravatar-enhanced' ),
		__( 'user', 'gravatar-enhanced' ),
		__( 'avatar', 'gravatar-enhanced' ),
		__( 'card', 'gravatar-enhanced' ),
	],
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
