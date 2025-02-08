import type { BlockEditProps } from '@wordpress/blocks';
import { createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import _debounce from 'lodash.debounce';
import { sha256 } from 'js-sha256';
import type { MainEditAttrs } from './shared-types';
import { Layout, UserTypes } from './shared-types';
import { getDefaultTemplate, getPortraitTemplate } from './editor-templates';
import { fetchProfile as basedFetchProfile, getExistingBlocks, validateEmail, getAvatarUrlWithSize } from './utils';
import clsx from 'clsx';

import './shared.scss';
import './edit.scss';

type ApiStatus = 'loading' | 'error' | 'success';

type Props = BlockEditProps< MainEditAttrs >;

export default function Edit( { attributes, setAttributes, clientId }: Props ) {
	const { layout, avatarUrlSizeParam, demoProfile, userType, userEmail, deletedElements } = attributes;

	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );
	const [ emailInputVal, setEmailInputVal ] = useState( '' );
	const [ apiStatus, setApiStatus ] = useState< ApiStatus >( 'loading' );
	const [ errorMsg, setErrorMsg ] = useState( '' );
	const prevExistingBlocksRef = useRef< string[] >( null );
	const deletedElementsRef = useRef( deletedElements ); // Avoid unnecessary `useEffect` calls.
	deletedElementsRef.current = deletedElements;
	const getTemplateRef = useRef( getDefaultTemplate ); // Avoid unnecessary `useEffect` calls.
	let defaultAvatarSize = 72;
	let layoutClassName = '';

	switch ( layout ) {
		case Layout.PORTRAIT:
			getTemplateRef.current = getPortraitTemplate;
			defaultAvatarSize = 354;
			layoutClassName = 'gravatar-block--portrait';
			break;
		case Layout.LANDSCAPE:
			layoutClassName = 'gravatar-block--landscape';
			break;
		case Layout.LINE:
			layoutClassName = 'gravatar-block--line';
			break;
	}

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

	// Fetch the profile data when the email changes.
	useEffect( () => {
		const fetchProfile = async ( email: string ) => {
			setApiStatus( 'loading' );
			setErrorMsg( '' );

			if ( ! validateEmail( email ) ) {
				setApiStatus( 'error' );
				setErrorMsg( __( 'Please enter a valid email in the block settings.', 'gravatar-enhanced' ) );
				return;
			}

			const hashedEmail = sha256( email.trim().toLowerCase() );
			const { error, data } = await basedFetchProfile( hashedEmail );

			if ( error ) {
				setApiStatus( 'error' );
				setErrorMsg( error );
			} else {
				setApiStatus( 'success' );

				data.avatar_url = getAvatarUrlWithSize( data.avatar_url, avatarUrlSizeParam || defaultAvatarSize );

				replaceInnerBlocks(
					clientId,
					createBlocksFromInnerBlocksTemplate( getTemplateRef.current( data, deletedElementsRef.current ) )
				);
			}
		};

		fetchProfile( userEmail );
	}, [ avatarUrlSizeParam, clientId, defaultAvatarSize, replaceInnerBlocks, userEmail ] );

	// Reset the block items from the navigation menu when the API status changes.
	useEffect( () => {
		if ( apiStatus !== 'success' ) {
			replaceInnerBlocks(
				clientId,
				demoProfile
					? createBlocksFromInnerBlocksTemplate( getTemplateRef.current( demoProfile, deletedElements ) )
					: []
			);
		}
	}, [ apiStatus, clientId, deletedElements, demoProfile, replaceInnerBlocks ] );

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
				className={ clsx( 'gravatar-block', layoutClassName, blockProps.className, {
					'gravatar-block--custom-text-color': !! blockProps.style.color,
				} ) }
			>
				{ apiStatus === 'loading' && (
					<div className="gravatar-block__loading">{ __( 'Loading…', 'gravatar-enhanced' ) }</div>
				) }
				{ apiStatus === 'error' && <div className="gravatar-block__error">{ errorMsg }</div> }
				{ apiStatus === 'success' && <InnerBlocks allowedBlocks={ [] } renderAppender={ undefined } /> }
				{ apiStatus !== 'success' && demoProfile && (
					<InnerBlocks
						allowedBlocks={ [] }
						renderAppender={ undefined }
						templateLock="all"
						template={ getTemplateRef.current( demoProfile, deletedElements ) }
					/>
				) }
			</div>
		</>
	);
}
