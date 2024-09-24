/**
 * External dependencies
 */
import type { BlockEditProps, TemplateArray } from '@wordpress/blocks';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect, useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import _debounce from 'lodash.debounce';

/**
 * Internal dependencies
 */
import { fetchProfile as basedFetchProfile } from '../utils';

enum UserTypeOptions {
	AUTHOR = 'author',
	USER = 'user',
	EMAIL = 'email',
}

interface BlockAttrs {
	userType: UserTypeOptions;
	userEmail: string;
}

export default function Edit( { attributes, setAttributes }: BlockEditProps< BlockAttrs > ) {
	const { userType, userEmail } = attributes;

	const [ emailInputVal, setEmailInputVal ] = useState( '' );
	const [ profileData, setProfileData ] = useState( null );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ errorMsg, setErrorMsg ] = useState( '' );

	const authorEmail = useSelect( ( select: SelectFn ) => {
		const postType = select( 'core/editor' ).getCurrentPostType();
		const postId = select( 'core/editor' ).getCurrentPostId();
		const authorId = select( 'core' ).getEntityRecord( 'postType', postType, postId )?.author;

		return select( 'core' ).getEntityRecord( 'root', 'user', authorId )?.email || '';
	}, [] );

	const userTypeOptions = [
		...( authorEmail ? [ { label: __( 'Author', 'gravatar-enhanced' ), value: UserTypeOptions.AUTHOR } ] : [] ),
		{ label: __( 'User', 'gravatar-enhanced' ), value: UserTypeOptions.USER },
		{ label: __( 'Custom email', 'gravatar-enhanced' ), value: UserTypeOptions.EMAIL },
	];

	const userNameOptions = useSelect( ( select: SelectFn ) => {
		const users = select( 'core' ).getUsers() || [];

		return users.map( ( { name, nickname, email } ) => ( { label: `${ name } (${ nickname })`, value: email } ) );
	}, [] );

	const debouncedSetUserEmail = useCallback(
		_debounce( ( email: string ) => setAttributes( { userEmail: email } ), 500 ),
		[]
	);

	useEffect( () => {
		// When the block is created, set the default email to the author's email.
		if ( userType === UserTypeOptions.AUTHOR && ! userEmail ) {
			setAttributes( { userEmail: authorEmail } );
		}
	}, [ authorEmail, setAttributes, userEmail, userType ] );

	useEffect( () => {
		const fetchProfile = async ( email: string ) => {
			setIsLoading( true );
			setErrorMsg( '' );

			const { error, data } = await basedFetchProfile( email );

			if ( error ) {
				setErrorMsg( error );
			} else {
				setProfileData( data );
			}

			setIsLoading( false );
		};

		fetchProfile( userEmail );
	}, [ userEmail ] );

	function handleUserTypeChange( type: UserTypeOptions ) {
		let email = '';

		if ( UserTypeOptions.AUTHOR ) {
			email = authorEmail;
		}
		if ( UserTypeOptions.USER ) {
			email = userNameOptions[ 0 ]?.value || '';
		}

		setAttributes( { userType: type, userEmail: email } );
		setEmailInputVal( '' );
	}

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
						text: __( 'View profile', 'gravatar-enhanced' ),
						className: 'gravatar-block-link--align-right',
						color: '#50575E',
					},
				],
			],
		],
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'gravatar-enhanced' ) }>
					<SelectControl
						label={ __( 'Select User Type', 'gravatar-enhanced' ) }
						value={ userType }
						options={ userTypeOptions }
						onChange={ handleUserTypeChange }
					/>
					{ userType === UserTypeOptions.USER && (
						<SelectControl
							label={ __( 'Select User', 'gravatar-enhanced' ) }
							hideLabelFromVision
							value={ userEmail }
							options={ userNameOptions }
							onChange={ ( email ) => setAttributes( { userEmail: email } ) }
						/>
					) }
					{ userType === UserTypeOptions.EMAIL && (
						<TextControl
							value={ emailInputVal }
							onChange={ ( email ) => {
								setEmailInputVal( email );
								debouncedSetUserEmail( email );
							} }
							placeholder={ __( 'Enter email', 'gravatar-enhanced' ) }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<div className="gravatar-block" style={ { borderRadius: '2px', backgroundColor: '#FFF' } }>
					<InnerBlocks allowedBlocks={ [] } template={ template } renderAppender={ undefined } />
				</div>
			</div>
		</>
	);
}
