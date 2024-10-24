import type { BlockEditProps, InnerBlockTemplate } from '@wordpress/blocks';
import { createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import _debounce from 'lodash.debounce';
import { sha256 } from 'js-sha256';
import clsx from 'clsx';
import type { Names as ElemNames } from './utils/get-existing-blocks';
import type { Attrs as ColumnAttrs } from './blocks/column/edit';
import type { Attrs as ImageAttrs } from './blocks/image/edit';
import type { Attrs as NameAttrs } from './blocks/name/edit';
import type { Attrs as ParagraphAttrs } from './blocks/paragraph/edit';
import type { Attrs as LinkAttrs } from './blocks/link/edit';
import { fetchProfile as basedFetchProfile, getExistingBlocks, validateEmail } from './utils';

import './shared.scss';
import './edit.scss';

export enum BlockNames {
	COLUMN = 'gravatar/block-column',
	IMAGE = 'gravatar/block-image',
	NAME = 'gravatar/block-name',
	PARAGRAPH = 'gravatar/block-paragraph',
	LINK = 'gravatar/block-link',
}

export enum KnownElemNames {
	AVATAR = 'avatar',
	DISPLAY_NAME = 'displayName',
	JOB = 'job',
	COMPANY = 'company',
	LOCATION = 'location',
	DESCRIPTION = 'description',
	GRAVATAR = 'gravatar',
	VIEW_PROFILE = 'viewProfile',
	HEADER = 'header',
	JOB_COMPANY_LOCATION_WRAPPER = 'jobCompanyLocationWrapper',
	JOB_COMPANY_WRAPPER = 'jobCompanyWrapper',
	FOOTER = 'footer',
}

export interface InnerBlockAttrsMap {
	[ BlockNames.COLUMN ]: ColumnAttrs;
	[ BlockNames.IMAGE ]: ImageAttrs;
	[ BlockNames.NAME ]: NameAttrs;
	[ BlockNames.PARAGRAPH ]: ParagraphAttrs;
	[ BlockNames.LINK ]: LinkAttrs;
}

type ApiStatus = 'loading' | 'error' | 'success';

enum UserTypes {
	AUTHOR = 'author',
	USER = 'user',
	EMAIL = 'email',
}

export interface Attrs {
	userType: UserTypes;
	userEmail: string;
	deletedElements: Record< string, boolean >;
}

export default function Edit( { attributes, setAttributes, clientId }: BlockEditProps< Attrs > ) {
	const { userType, userEmail, deletedElements } = attributes;

	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );
	const [ emailInputVal, setEmailInputVal ] = useState( userEmail );
	const [ apiStatus, setApiStatus ] = useState< ApiStatus >( 'loading' );
	const [ errorMsg, setErrorMsg ] = useState( '' );
	const prevExistingBlocksRef = useRef< ElemNames >( null );
	// Avoid get template related functions to be re-created on every render.
	const deletedElementsRef = useRef( deletedElements );
	deletedElementsRef.current = deletedElements;

	const blockProps = useBlockProps();

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

	const users = useSelect( ( select: SelectFn ) => select( 'core' ).getUsers() || [], [] );

	const userNameOptions = users.map( ( { name, nickname, email } ) => ( {
		label: `${ name } (${ nickname })`,
		value: email,
	} ) );

	const firstUserEmail = userNameOptions[ 0 ]?.value || '';

	// In v0.1.0, `userValue` is migrated to `userEmail`, which may be a user ID.
	useEffect( () => {
		if ( userType !== UserTypes.USER || isNaN( Number( userEmail ) ) || ! users.length ) {
			return;
		}

		const userId = Number( userEmail );
		const { email } = users.find( ( { id } ) => id === userId ) || {};

		if ( email ) {
			setAttributes( { userEmail: email } );
		} else {
			// If the user email is not found, use the first user's email as a fallback.
			setAttributes( { userEmail: firstUserEmail } );
		}
	}, [ firstUserEmail, setAttributes, userEmail, userType, users ] );

	const isEditingTemplate = useSelect( ( select: SelectFn ) => select( 'core/edit-site' )?.isPage() === false, [] );

	// When the block is created, set the `userType` and `userEmail` based on the available data.
	useEffect( () => {
		if ( userType === UserTypes.EMAIL || userEmail ) {
			return;
		}

		// When first time to edit a template, the `authorEmail` is not available. Use the first user's email as a fallback.
		if ( isEditingTemplate && userType === UserTypes.AUTHOR ) {
			setAttributes( { userType: UserTypes.USER, userEmail: firstUserEmail } );
		} else {
			setAttributes( { userEmail: authorEmail } );
		}
	}, [ authorEmail, firstUserEmail, isEditingTemplate, setAttributes, userEmail, userType ] );

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
				const nextDeletedElements = { ...deletedElements };

				prevExistingBlocksRef.current.forEach( ( name ) => {
					if ( ! currExistingBlocks.includes( name ) ) {
						nextDeletedElements[ name ] = true;
					}
				} );

				setAttributes( { deletedElements: nextDeletedElements } );
			}

			prevExistingBlocksRef.current = currExistingBlocks;
		},
		[ apiStatus, clientId, deletedElements, setAttributes ]
	);

	const getBlockTempate = useCallback(
		< T extends BlockNames >(
			blockName: T,
			elemName: string,
			attrs: InnerBlockAttrsMap[ T ],
			innerBlocks?: T extends BlockNames.COLUMN ? InnerBlockTemplate[] : never
		): InnerBlockTemplate | null => {
			if ( deletedElementsRef.current[ elemName ] ) {
				return null;
			}

			let filteredBlocks: InnerBlockTemplate[] = [];

			if ( Array.isArray( innerBlocks ) ) {
				filteredBlocks = innerBlocks.filter( Boolean );

				if ( ! filteredBlocks.length ) {
					return null;
				}
			}

			// Give the block a unique name for the deleted elements to work.
			return [ blockName, { name: elemName, ...attrs }, filteredBlocks ];
		},
		[]
	);

	/* eslint-disable camelcase */
	// TODO: More templates to be added for different patterns.
	const getTemplate = useCallback(
		( profileData: GravatarAPIProfile ): InnerBlockTemplate[] => {
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
			} = profileData;

			// TODO: Reuse these main UI elements to compose patterns.
			const avatar =
				avatar_url &&
				getBlockTempate( BlockNames.IMAGE, KnownElemNames.AVATAR, {
					className: 'gravatar-block-image--avatar',
					linkUrl: profile_url,
					imageUrl: avatar_url,
					imageWidth: 72,
					imageHeight: 72,
					imageAlt: avatar_alt_text || display_name,
				} );

			const displayName =
				display_name &&
				getBlockTempate( BlockNames.NAME, KnownElemNames.DISPLAY_NAME, {
					className: 'gravatar-text-truncate-2-lines',
					text: display_name,
				} );

			const jobTitle =
				job_title &&
				getBlockTempate( BlockNames.PARAGRAPH, KnownElemNames.JOB, {
					className: 'gravatar-block-paragraph--job',
					text: job_title,
				} );

			const company =
				com &&
				getBlockTempate( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, {
					className: 'gravatar-block-paragraph--company',
					text: com,
				} );

			const location =
				loc &&
				getBlockTempate( BlockNames.PARAGRAPH, KnownElemNames.LOCATION, {
					className: 'gravatar-block-paragraph--location gravatar-text-truncate-1-line',
					text: loc,
				} );

			const description =
				desc &&
				getBlockTempate( BlockNames.PARAGRAPH, KnownElemNames.DESCRIPTION, {
					className: 'gravatar-text-truncate-2-lines',
					text: desc,
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
			const verifiedAccounts = verified_accounts.map(
				( { url, service_icon, service_label, is_hidden } ) =>
					! is_hidden &&
					getBlockTempate( BlockNames.IMAGE, service_label, {
						linkUrl: url,
						imageUrl: service_icon,
						imageWidth: 32,
						imageHeight: 32,
						imageAlt: service_label,
					} )
			);

			const viewProfile =
				profile_url &&
				getBlockTempate( BlockNames.LINK, KnownElemNames.VIEW_PROFILE, {
					className: 'gravatar-block-link--align-right',
					linkUrl: profile_url,
					text: __( 'View profile →', 'gravatar-enhanced' ),
				} );

			return [
				getBlockTempate(
					BlockNames.COLUMN,
					KnownElemNames.HEADER,
					{ className: 'gravatar-block-column--header gravatar-block-column--align-center' },
					[
						avatar,
						getBlockTempate(
							BlockNames.COLUMN,
							KnownElemNames.JOB_COMPANY_LOCATION_WRAPPER,
							{ linkUrl: profile_url, verticalAlignment: true },
							[
								displayName,
								getBlockTempate(
									BlockNames.COLUMN,
									KnownElemNames.JOB_COMPANY_WRAPPER,
									{ className: 'gravatar-block-column--comma-separated' },
									[ jobTitle, company ]
								),
								location,
							]
						),
					]
				),
				description,
				getBlockTempate(
					BlockNames.COLUMN,
					KnownElemNames.FOOTER,
					{ className: 'gravatar-block-column--footer gravatar-block-column--align-center' },
					[ ...verifiedAccounts, viewProfile ]
				),
			].filter( Boolean );
		},
		[ getBlockTempate ]
	);
	/* eslint-enable camelcase */

	// Fetch the profile data when the email changes.
	useEffect( () => {
		const fetchProfile = async ( email: string ) => {
			setApiStatus( 'loading' );
			setErrorMsg( '' );

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

				const blocks = createBlocksFromInnerBlocksTemplate( getTemplate( data ) );
				replaceInnerBlocks( clientId, blocks );
			}
		};

		fetchProfile( userEmail );
	}, [ clientId, getTemplate, replaceInnerBlocks, userEmail ] );

	// Reset the block items from the navigation menu when the API status changes.
	useEffect( () => {
		if ( apiStatus !== 'success' ) {
			replaceInnerBlocks( clientId, [] );
		}
	}, [ apiStatus, clientId, replaceInnerBlocks ] );

	function handleUserTypeChange( type: UserTypes ) {
		let email = '';

		if ( type === UserTypes.AUTHOR ) {
			email = authorEmail;
		}
		if ( type === UserTypes.USER ) {
			email = firstUserEmail;
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
			<div
				{ ...blockProps }
				className={ clsx( 'gravatar-block', blockProps.className, {
					'gravatar-block--custom-text-color': !! blockProps.style.color,
				} ) }
			>
				{ apiStatus === 'loading' && <div>{ __( 'Loading…', 'gravatar-enhanced' ) }</div> }
				{ apiStatus === 'error' && <div>{ errorMsg }</div> }
				{ apiStatus === 'success' && <InnerBlocks allowedBlocks={ [] } renderAppender={ undefined } /> }
			</div>
		</>
	);
}
