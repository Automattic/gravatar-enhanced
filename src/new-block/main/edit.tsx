/**
 * External dependencies
 */
import type { BlockEditProps, InnerBlockTemplate } from '@wordpress/blocks';
import { createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import _debounce from 'lodash.debounce';
import { sha256 } from 'js-sha256';

/**
 * Internal dependencies
 */
import type { AttrNames } from '../utils/get-existing-blocks';
import { fetchProfile as basedFetchProfile, getExistingBlocks, validateEmail } from '../utils';

export enum BlockNames {
	COLUMN = 'gravatar/block-column',
	IMAGE = 'gravatar/block-image',
	NAME = 'gravatar/block-name',
	PARAGRAPH = 'gravatar/block-paragraph',
	LINK = 'gravatar/block-link',
}

type BlockName = `${ BlockNames }`;

type ApiStatus = 'loading' | 'error' | 'success';

enum UserTypes {
	AUTHOR = 'author',
	USER = 'user',
	EMAIL = 'email',
}

interface BlockAttrs {
	userType: UserTypes;
	userEmail: string;
	deletedElements: Record< string, boolean >;
}

export default function Edit( { attributes, setAttributes, clientId }: BlockEditProps< BlockAttrs > ) {
	const { userType, userEmail, deletedElements } = attributes;

	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );
	const [ emailInputVal, setEmailInputVal ] = useState( userEmail );
	const [ apiStatus, setApiStatus ] = useState< ApiStatus >( 'loading' );
	const [ errorMsg, setErrorMsg ] = useState( '' );
	const [ profileData, setProfileData ] = useState< GravatarAPIProfile >( null );
	const prevExistingBlocksRef = useRef< AttrNames >( null );

	const authorEmail = useSelect( ( select: SelectFn ) => {
		const postType = select( 'core/editor' ).getCurrentPostType();
		const postId = select( 'core/editor' ).getCurrentPostId();
		const authorId = select( 'core' ).getEntityRecord( 'postType', postType, postId )?.author;

		return select( 'core' ).getEntityRecord( 'root', 'user', authorId )?.email || '';
	}, [] );

	const userTypeOptions = [
		...( authorEmail ? [ { label: __( 'Author', 'gravatar-enhanced' ), value: UserTypes.AUTHOR } ] : [] ),
		{ label: __( 'User', 'gravatar-enhanced' ), value: UserTypes.USER },
		{ label: __( 'Custom email', 'gravatar-enhanced' ), value: UserTypes.EMAIL },
	];

	const userNameOptions = useSelect( ( select: SelectFn ) => {
		const users = select( 'core' ).getUsers() || [];

		return users.map( ( { name, nickname, email } ) => ( { label: `${ name } (${ nickname })`, value: email } ) );
	}, [] );

	// When the block is created, set the default email to the author's email.
	useEffect( () => {
		if ( userType === UserTypes.AUTHOR && ! userEmail ) {
			setAttributes( { userEmail: authorEmail } );
		}
	}, [ authorEmail, setAttributes, userEmail, userType ] );

	// Set the deleted elements when the inner blocks change.
	useSelect(
		( select: SelectFn ) => {
			if ( apiStatus !== 'success' ) {
				prevExistingBlocksRef.current = null;
				return;
			}

			const { innerBlocks = [] } = select( 'core/block-editor' ).getBlock( clientId );
			const currExistingBlocks = getExistingBlocks( innerBlocks );

			if ( prevExistingBlocksRef.current?.length > currExistingBlocks.length ) {
				const newDeletedElements = { ...deletedElements };

				prevExistingBlocksRef.current.forEach( ( name ) => {
					if ( ! currExistingBlocks.includes( name ) ) {
						newDeletedElements[ name ] = true;
					}
				} );

				setAttributes( { deletedElements: newDeletedElements } );
			}

			prevExistingBlocksRef.current = currExistingBlocks;
		},
		[ apiStatus, clientId, deletedElements, setAttributes ]
	);

	// Fetch the profile data when the email changes.
	useEffect( () => {
		const fetchProfile = async ( email: string ) => {
			setApiStatus( 'loading' );
			setErrorMsg( '' );
			setProfileData( null );

			if ( ! validateEmail( email ) ) {
				setApiStatus( 'error' );
				setErrorMsg( __( 'Please enter a valid email.', 'gravatar-enhanced' ) );
				return;
			}

			const hashedEmail = sha256( email.trim().toLowerCase() );
			const { error, data } = await basedFetchProfile( hashedEmail );

			if ( error ) {
				setApiStatus( 'error' );
				setErrorMsg( error );
			} else {
				setApiStatus( 'success' );
				setProfileData( data );
			}
		};

		fetchProfile( userEmail );
	}, [ userEmail ] );

	const getBlockTempate = useCallback(
		(
			name: BlockName,
			attrs: { name: string } & Record< string, any >,
			innerBlocks: InnerBlockTemplate[] = []
		): InnerBlockTemplate | null => {
			if ( deletedElements[ attrs?.name ] ) {
				return null;
			}

			innerBlocks = innerBlocks.filter( Boolean );
			const isEmptyCol = name === BlockNames.COLUMN && ! innerBlocks.length;

			if ( isEmptyCol ) {
				return null;
			}

			return [ name, attrs, innerBlocks ];
		},
		[ deletedElements ]
	);

	/* eslint-disable camelcase */
	const getTemplate = useCallback( (): InnerBlockTemplate[] => {
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

		// TODO: Reuse these main UI elements to compose patterns.
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

		const location =
			loc && getBlockTempate( 'gravatar/block-paragraph', { name: 'location', text: loc, color: '#50575E' } );

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
					avatar,
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
							location,
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
	}, [ getBlockTempate, profileData ] );
	/* eslint-enable camelcase */

	// Update inner blocks when the profile data changes.
	useEffect( () => {
		replaceInnerBlocks( clientId, createBlocksFromInnerBlocksTemplate( getTemplate() ) );
	}, [ clientId, getTemplate, replaceInnerBlocks ] );

	function handleUserTypeChange( type: UserTypes ) {
		let email = '';

		if ( type === UserTypes.AUTHOR ) {
			email = authorEmail;
		}
		if ( type === UserTypes.USER ) {
			email = userNameOptions[ 0 ]?.value || '';
		}

		setAttributes( { userType: type, userEmail: email } );
		setEmailInputVal( '' );
	}

	// eslint-disable-next-line react-hooks/exhaustive-deps
	const debouncedSetUserEmail = useCallback(
		_debounce( ( email: string ) => setAttributes( { userEmail: email } ), 500 ),
		[]
	);

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
					{ userType === UserTypes.USER && (
						<SelectControl
							value={ userEmail }
							options={ userNameOptions }
							onChange={ ( email ) => setAttributes( { userEmail: email } ) }
						/>
					) }
					{ userType === UserTypes.EMAIL && (
						<TextControl
							type="email"
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
					{ apiStatus === 'loading' && <div>{ __( 'Loading…', 'gravatar-enhanced' ) }</div> }
					{ apiStatus === 'error' && <div>{ errorMsg }</div> }
					{ apiStatus === 'success' && <InnerBlocks allowedBlocks={ [] } renderAppender={ undefined } /> }
				</div>
			</div>
		</>
	);
}
