import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { KnownElemNames } from '../../edit';
import Edit from './edit';
import { ParagraphIcon } from '../../components';
import metadata from './block.json';

const title = __( 'Paragraph', 'gravatar-enhanced' );

const blockConfig: BlockConfiguration = {
	...metadata,
	icon: ParagraphIcon,
	// @ts-ignore
	__experimentalLabel: ( { name } ) => {
		switch ( name ) {
			case KnownElemNames.JOB:
				return __( 'Job Title', 'gravatar-enhanced' );
			case KnownElemNames.COMPANY:
				return __( 'Company', 'gravatar-enhanced' );
			case KnownElemNames.LOCATION:
				return __( 'Location', 'gravatar-enhanced' );
			case KnownElemNames.DESCRIPTION:
				return __( 'About Me', 'gravatar-enhanced' );
			default:
				return title;
		}
	},
	title,
	description: __( 'The Paragraph block for the Gravatar block.', 'gravatar-enhanced' ),
	edit: Edit,
};

registerBlockType( metadata.name, blockConfig );
