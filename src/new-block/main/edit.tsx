import { BlockEditProps, TemplateArray } from '@wordpress/blocks'; 
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import clsx from 'clsx';
import { textdomain } from './block.json';

interface BlockAttrs {
	// TODO...
}

export default function Edit( { attributes }: BlockEditProps< BlockAttrs > ) {
	const blockProps = useBlockProps();

	const template: TemplateArray = [
		[
			'gravatar/block-column',
			{ className: 'gravatar-block-column--align-center' },
			[
				[
					'gravatar/block-column',
					{},
					[
						[
							'gravatar/block-image',
							{
								// TODO: Don't forget the `utm_source`.
								linkUrl: 'https://gravatar.com/wellyshen?utm_source=gravatar-block',
								imageUrl:
									'https://gravatar.com/avatar/c3bb8d897bb538896708195dd9eb162f585654611c50a3a1c9a16a7b64f33270',
								imageWidth: 72,
								imageHeight: 72,
								imageAlt: 'Welly Shen',
								className: 'gravatar-block-image--avatar',
							},
						],
					],
				],
				[
					'gravatar/block-column',
					{
						linkUrl: 'https://gravatar.com/wellyshen?utm_source=gravatar-block',
						verticalAlignment: true,
					},
					[
						// TODO: Lock this block.
						[
							'gravatar/block-name',
							{
								text: 'Welly Shen',
								className: 'gravatar-block-text-truncate-2-lines',
								color: '#101517',
							},
						],
						[
							'gravatar/block-column',
							{
								className: 'gravatar-block-column--comma-separated',
								color: '#50575E',
							},
							[
								[ 'gravatar/block-paragraph', { text: 'Software Engineer' } ],
								[ 'gravatar/block-paragraph', { text: 'Automattic' } ],
							],
						],
						[
							'gravatar/block-column',
							{
								className: 'gravatar-block-column--comma-separated',
								color: '#50575E',
							},
							[ [ 'gravatar/block-paragraph', { text: 'Taipei, Taiwan' } ] ],
						],
					],
				],
			],
		],
		[
			'gravatar/block-paragraph',
			{
				text: "I'm a bird in the sky 🪽.",
				className: 'gravatar-block-text-truncate-2-lines',
				color: '#101517',
			},
		],
		[
			'gravatar/block-column',
			{ className: 'gravatar-block-column--footer gravatar-block-column--align-center' },
			[
				[
					'gravatar/block-image',
					{
						linkUrl: 'https://gravatar.com/wellyshen?utm_source=gravatar-block',
						imageUrl: 'https://secure.gravatar.com/icons/gravatar.svg',
						imageWidth: 32,
						imageHeight: 32,
						imageAlt: 'Gravatar',
					},
				],
				[
					'gravatar/block-image',
					{
						linkUrl: 'https://wellyshen.wordpress.com',
						imageUrl: 'https://gravatar.com/icons/wordpress.svg',
						imageWidth: 32,
						imageHeight: 32,
						imageAlt: 'WordPress',
					},
				],
				[
					'gravatar/block-image',
					{
						linkUrl: 'https://www.instagram.com/welly_shen',
						imageUrl: 'https://gravatar.com/icons/instagram.svg',
						imageWidth: 32,
						imageHeight: 32,
						imageAlt: 'Instagram',
					},
				],
				[
					'gravatar/block-image',
					{
						linkUrl: 'https://twitter.com/welly_shen',
						imageUrl: 'https://gravatar.com/icons/twitter-alt.svg',
						imageWidth: 32,
						imageHeight: 32,
						imageAlt: 'X',
					},
				],
				[
					'gravatar/block-link',
					{
						linkUrl: 'https://gravatar.com/wellyshen?utm_source=gravatar-block',
						text: __( 'View profile', textdomain ),
						className: 'gravatar-block-link--at-right',
						color: '#50575E',
					},
				],
			],
		],
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', textdomain ) }>{ /* TODO... */ }</PanelBody>
			</InspectorControls>
			<div
				{ ...blockProps }
				className={ clsx( 'gravatar-block', blockProps.className ) }
				style={ { borderRadius: '2px', backgroundColor: '#FFF' } }
			>
				<InnerBlocks allowedBlocks={ [] } template={ template } renderAppender={ undefined } />
			</div>
		</>
	);
}
