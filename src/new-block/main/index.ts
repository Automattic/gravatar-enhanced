import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import { GravatarCardIcon } from '../components';
import metadata from './block.json';

import './style.scss';

const textdomain = metadata.textdomain;

const blockConfig: BlockConfiguration = {
	...metadata,
	icon: GravatarCardIcon,
	title: __( 'Gravatar Card', textdomain ),
	description: __( 'Display user information directly from Gravatar Public Profiles.', textdomain ),
	keywords: [
		__( 'gravatar', textdomain ),
		__( 'profile', textdomain ),
		__( 'author', textdomain ),
		__( 'user', textdomain ),
		__( 'avatar', textdomain ),
		__( 'card', textdomain ),
	],
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
