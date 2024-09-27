/**
 * External dependencies
 */
import type { BlockEditProps, Template, TemplateArray } from '@wordpress/blocks';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import _debounce from 'lodash.debounce';

/**
 * Internal dependencies
 */
import { fetchProfile as basedFetchProfile, getExistingElements } from '../utils';

enum UserTypeOptions {
	AUTHOR = 'author',
	USER = 'user',
	EMAIL = 'email',
}

interface BlockAttrs {
	userType: UserTypeOptions;
	userEmail: string;
	deletedElements: Record< string, boolean >;
}

export default function Edit( { attributes, setAttributes, clientId }: BlockEditProps< BlockAttrs > ) {
	const { userType, userEmail, deletedElements } = attributes;

	const [ profileData, setProfileData ] = useState< GravatarAPIProfile >( null );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ errorMsg, setErrorMsg ] = useState( '' );
	const emailInputRef = useRef< HTMLInputElement >( null );

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

	/* useSelect(
		( select: SelectFn ) => {
			const blocks = select( 'core/block-editor' ).getBlock( clientId )?.innerBlocks || [];
			const existingElements = getExistingElements( blocks );

			if ( Object.keys( existingElements ).length !== Object.keys( deletedElements ).length ) {
				setAttributes( { deletedElements: existingElements } );
			}
		},
		[ clientId, deletedElements, setAttributes ]
	); */

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
		emailInputRef.current.value = '';
	}

	// TODO: useCallback?
	function getBlockTempate(
		blockName: string,
		attrs: { name: string } & Record< string, any >,
		innerBlocks: TemplateArray = []
	): Template | null {
		if ( deletedElements[ attrs?.name ] ) {
			return null;
		}

		innerBlocks = innerBlocks.filter( Boolean );
		const isEmptyCol = blockName === 'gravatar/block-column' && ! innerBlocks.length;

		if ( isEmptyCol ) {
			return null;
		}

		return [ blockName, attrs, innerBlocks ];
	}

	/* eslint-disable camelcase */
	function getTemplate(): TemplateArray {
		let {
			avatar_url,
			avatar_alt_text,
			profile_url,
			display_name,
			job_title,
			company: com,
			location: loc,
			description: desc,
			verified_accounts = [],
		} = profileData || {};

		const avatar =
			avatar_url &&
			getBlockTempate( 'gravatar/block-image', {
				name: 'avatar',
				linkUrl: profile_url,
				imageUrl: avatar_url,
				imageWidth: 72,
				imageHeight: 72,
				imageAlt: avatar_alt_text || display_name,
				className: 'gravatar-block-image--avatar',
			} );

		const displayName =
			display_name &&
			getBlockTempate( 'gravatar/block-name', {
				name: 'displayName',
				text: display_name,
				className: 'gravatar-block-text-truncate-2-lines',
				color: '#101517',
			} );

		const jobTitle = job_title && getBlockTempate( 'gravatar/block-paragraph', { name: 'job', text: job_title } );

		const company = com && getBlockTempate( 'gravatar/block-paragraph', { name: 'company', text: com } );

		const location = loc && getBlockTempate( 'gravatar/block-paragraph', { name: 'location', text: loc } );

		const description =
			desc &&
			getBlockTempate( 'gravatar/block-paragraph', {
				name: 'description',
				text: desc,
				className: 'gravatar-block-text-truncate-2-lines',
				color: '#101517',
			} );

		verified_accounts = [
			{
				url: profile_url,
				service_type: 'gravatar',
				service_icon: 'https://secure.gravatar.com/icons/gravatar.svg',
				service_label: 'Gravatar',
				is_hidden: false,
			},
			...verified_accounts,
		];
		const verifiedAccounts = verified_accounts
			.map(
				( { url, service_type, service_icon, service_label, is_hidden } ) =>
					! is_hidden &&
					getBlockTempate( 'gravatar/block-image', {
						name: service_type,
						linkUrl: url,
						imageUrl: service_icon,
						imageWidth: 32,
						imageHeight: 32,
						imageAlt: service_label,
					} )
			)
			.filter( Boolean );

		const viewProfile = getBlockTempate( 'gravatar/block-link', {
			name: 'viewProfile',
			linkUrl: profile_url,
			text: __( 'View profile', 'gravatar-enhanced' ),
			className: 'gravatar-block-link--align-right',
			color: '#50575E',
		} );

		return [
			getBlockTempate(
				'gravatar/block-column',
				{ name: 'header', className: 'gravatar-block-column--header gravatar-block-column--align-center' },
				[
					getBlockTempate( 'gravatar/block-column', { name: 'avatarWrapper' }, [ avatar ] ),
					getBlockTempate(
						'gravatar/block-column',
						{
							name: 'jobCompanyLocationWrapper',
							linkUrl: profile_url,
							verticalAlignment: true,
						},
						[
							displayName,
							getBlockTempate(
								'gravatar/block-column',
								{
									name: 'jobCompanyWrapper',
									className: 'gravatar-block-column--comma-separated',
									color: '#50575E',
								},
								[ jobTitle, company ]
							),
							getBlockTempate(
								'gravatar/block-column',
								{
									name: 'locationWrapper',
									className: 'gravatar-block-column--comma-separated',
									color: '#50575E',
								},
								[ location ]
							),
						]
					),
				]
			),
			description,
			getBlockTempate(
				'gravatar/block-column',
				{
					name: 'footer',
					className: 'gravatar-block-column--footer gravatar-block-column--align-center',
				},
				[ ...verifiedAccounts, viewProfile ]
			),
		].filter( Boolean );
	}
	/* eslint-enable camelcase */

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'gravatar-enhanced' ) }>
					<SelectControl
						label={ __( 'Select User', 'gravatar-enhanced' ) }
						value={ userType }
						options={ userTypeOptions }
						onChange={ handleUserTypeChange }
					/>
					{ userType === UserTypeOptions.USER && (
						<SelectControl
							value={ userEmail }
							options={ userNameOptions }
							onChange={ ( email ) => setAttributes( { userEmail: email } ) }
						/>
					) }
					{ userType === UserTypeOptions.EMAIL && (
						// @ts-ignore
						<TextControl
							type="email"
							onChange={ ( email ) => debouncedSetUserEmail( email ) }
							placeholder={ __( 'Enter email', 'gravatar-enhanced' ) }
							ref={ emailInputRef }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				{ isLoading && <div>{ __( 'Loading…', 'gravatar-enhanced' ) }</div> }
				{ errorMsg && <div>{ errorMsg }</div> }
				{ profileData && (
					<div className="gravatar-block" style={ { borderRadius: '2px', backgroundColor: '#FFF' } }>
						<InnerBlocks allowedBlocks={ [] } template={ getTemplate() } renderAppender={ undefined } />
					</div>
				) }
			</div>
		</>
	);
}
