import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import { GravatarCardIcon } from '../components';
import metadata from './block.json';

import './style.scss';

registerBlockType( metadata.name, {
	icon: GravatarCardIcon,
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
