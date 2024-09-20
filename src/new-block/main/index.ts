import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import { GravatarCardIcon } from '../components';
import metadata from './block.json';

import './style.scss';

registerBlockType( metadata.name, {
	...metadata,
	icon: GravatarCardIcon,
	title: __( 'Gravatar Card', 'gravatar-enhanced' ),
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
} );
