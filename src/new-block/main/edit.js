import { __ } from '@wordpress/i18n';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import classNames from 'classnames';

export default function Edit() {
	const blockProps = useBlockProps();

	const template = [
		[
			'gravatar/block-column',
			{ className: 'gravatar-block-column--align-center' },
			[
				[
					'gravatar/block-column',
					{ className: 'gravatar-block-column--avatar' },
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
						[ 'gravatar/block-name', { text: 'Welly Shen', color: '#101517' } ],
						[
							'gravatar/block-column',
							{ className: 'gravatar-block-column--comma-separated', color: '#50575E' },
							[
								[ 'gravatar/block-paragraph', { text: 'Software Engineer' } ],
								[ 'gravatar/block-paragraph', { text: 'Automattic' } ],
							],
						],
						[
							'gravatar/block-column',
							{ className: 'gravatar-block-column--comma-separated', color: '#50575E' },
							[
								[ 'gravatar/block-paragraph', { text: 'Taipei' } ],
								[ 'gravatar/block-paragraph', { text: 'Taiwan' } ],
							],
						],
					],
				],
			],
		],
		[ 'gravatar/block-paragraph', { text: "I'm a bird in the sky 🪽", color: '#101517' } ],
		[
			'gravatar/block-column',
			{ className: 'gravatar-block-column--verified-accounts gravatar-block-column--align-center' },
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
					// TODO: Lock this block.
					'gravatar/block-link',
					{
						linkUrl: 'https://gravatar.com/wellyshen?utm_source=gravatar-block',
						text: __( 'View profile', 'gravatar-enhanced' ),
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
				<PanelBody title={ __( 'Settings', 'gravatar-enhanced' ) }>{ /* TODO... */ }</PanelBody>
			</InspectorControls>
			<div
				{ ...blockProps }
				className={ classNames( 'gravatar-block', blockProps.className ) }
				style={ { borderRadius: '2px', backgroundColor: '#FFF' } }
			>
				<InnerBlocks allowedBlocks={ [] } template={ template } renderAppender={ false } />
			</div>
		</>
	);
}