/* eslint-disable camelcase */

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

	function getBlockTempate(
		blockName: string,
		elementName: string,
		attrs?: Record< string, any >,
		innerBlocks: TemplateArray = []
	): Template | null {
		return ! deletedElements[ elementName ]
			? [ blockName, { ...attrs, name: elementName }, innerBlocks.filter( Boolean ) ]
			: null;
	}

	function getTemplate(): TemplateArray {
		const {
			avatar_url,
			avatar_alt_text,
			profile_url,
			display_name,
			job_title,
			company: companyData,
			location: locationData,
			description: descriptionData,
			verified_accounts = [],
		} = profileData || {};

		const avatar =
			avatar_url &&
			getBlockTempate( 'gravatar/block-image', 'avatar', {
				linkUrl: profile_url,
				imageUrl: avatar_url,
				imageWidth: 72,
				imageHeight: 72,
				imageAlt: avatar_alt_text || display_name,
				className: 'gravatar-block-image--avatar',
			} );

		const displayName =
			display_name &&
			getBlockTempate( 'gravatar/block-name', 'displayName', {
				text: display_name,
				className: 'gravatar-block-text-truncate-2-lines',
				color: '#101517',
			} );

		const jobTitle = job_title && getBlockTempate( 'gravatar/block-paragraph', 'jobTitle', { text: job_title } );

		const company = companyData && getBlockTempate( 'gravatar/block-paragraph', 'company', { text: companyData } );

		const location =
			locationData && getBlockTempate( 'gravatar/block-paragraph', 'location', { text: locationData } );

		const avatarWrapper = avatar && getBlockTempate( 'gravatar/block-column', 'avatarWrapper', {}, [ avatar ] );

		const jobTitleAndCompanyWrapper =
			( jobTitle || company ) &&
			getBlockTempate(
				'gravatar/block-column',
				'jobTitleAndCompanyWrapper',
				{
					className: 'gravatar-block-column--comma-separated',
					color: '#50575E',
				},
				[ jobTitle, company ]
			);

		const locationWrapper =
			location &&
			getBlockTempate(
				'gravatar/block-column',
				'locationWrapper',
				{
					className: 'gravatar-block-column--comma-separated',
					color: '#50575E',
				},
				[ location ]
			);

		const userInfo =
			( displayName || jobTitleAndCompanyWrapper || locationWrapper ) &&
			getBlockTempate(
				'gravatar/block-column',
				'userInfo',
				{
					linkUrl: profile_url,
					verticalAlignment: true,
				},
				[ displayName, jobTitleAndCompanyWrapper, locationWrapper ]
			);

		const header =
			( avatarWrapper || userInfo ) &&
			getBlockTempate(
				'gravatar/block-column',
				'header',
				{ className: 'gravatar-block-column--header gravatar-block-column--align-center' },
				[ avatarWrapper, userInfo ]
			);

		const description =
			descriptionData &&
			getBlockTempate( 'gravatar/block-paragraph', 'description', {
				text: descriptionData,
				className: 'gravatar-block-text-truncate-2-lines',
				color: '#101517',
			} );

		const gravatar = getBlockTempate( 'gravatar/block-image', 'gravatar', {
			linkUrl: profile_url,
			imageUrl: 'https://secure.gravatar.com/icons/gravatar.svg',
			imageWidth: 32,
			imageHeight: 32,
			imageAlt: 'Gravatar',
		} );

		const verifiedAccounts = verified_accounts.map(
			( { url, service_type, service_icon, service_label, is_hidden } ) =>
				! is_hidden &&
				getBlockTempate( 'gravatar/block-image', service_type, {
					linkUrl: url,
					imageUrl: service_icon,
					imageWidth: 32,
					imageHeight: 32,
					imageAlt: service_label,
				} )
		);

		const viewProfile = getBlockTempate( 'gravatar/block-link', 'viewProfile', {
			linkUrl: profile_url,
			text: __( 'View profile', 'gravatar-enhanced' ),
			className: 'gravatar-block-link--align-right',
			color: '#50575E',
		} );

		const footer =
			( gravatar || verifiedAccounts.length || viewProfile ) &&
			getBlockTempate(
				'gravatar/block-column',
				'footer',
				{
					className: 'gravatar-block-column--footer gravatar-block-column--align-center',
				},
				[ gravatar, ...verifiedAccounts, viewProfile ]
			);

		return [ header, description, footer ].filter( Boolean );
	}

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
