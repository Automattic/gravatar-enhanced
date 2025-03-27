import type { BlockEditProps } from '@wordpress/blocks';
import { createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import _debounce from 'lodash.debounce';
import { sha256 } from 'js-sha256';
import clsx from 'clsx';
import type { MainEditAttrs } from './shared-types';
import { Layout, UserTypes } from './shared-types';
import { getDefaultTemplate, getPortraitTemplate } from './editor-templates';
import { fetchProfile as basedFetchProfile, getExistingBlocks, getAvatarUrlWithSize } from './utils';
import isEmail from '../shared/is-email';

import './shared.scss';
import './edit.scss';

type ApiStatus = 'loading' | 'error' | 'success';

type Props = BlockEditProps< MainEditAttrs >;

export default function Edit( { context: { postType, postId }, attributes, setAttributes, clientId }: Props ) {
	const {
		layout,
		avatarUrlSizeParam,
		placeholderProfile,
		isChildBlockClickable,
		userType,
		userEmail,
		deletedElements,
	} = attributes;

	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );
	const [ emailInputVal, setEmailInputVal ] = useState( userEmail );
	const [ apiStatus, setApiStatus ] = useState< ApiStatus >();
	const [ errorMsg, setErrorMsg ] = useState( '' );
	const [ errorCode, setErrorCode ] = useState< number >();
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

	const userTypeOptions = [
		{ label: __( 'Author', 'gravatar-enhanced' ), value: UserTypes.AUTHOR },
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
		setApiStatus( undefined );
		setErrorCode( undefined );
		setErrorMsg( '' );

		const trimmedEmail = userEmail.trim();

		if ( ! trimmedEmail ) {
			return;
		}

		const fetchProfile = async ( email: string ) => {
			setApiStatus( 'loading' );

			if ( ! isEmail( email ) ) {
				setApiStatus( 'error' );
				setErrorMsg( __( 'Please enter a valid email.', 'gravatar-enhanced' ) );
				return;
			}

			const hashedEmail = sha256( email.toLowerCase() );
			const { errorCode: errCode, errorMsg: errMsg, data } = await basedFetchProfile( hashedEmail );

			if ( errCode ) {
				setApiStatus( 'error' );
				setErrorCode( errCode );
				setErrorMsg( errMsg );
			} else {
				setApiStatus( 'success' );

				data.avatar_url = getAvatarUrlWithSize( data.avatar_url, avatarUrlSizeParam || defaultAvatarSize );

				replaceInnerBlocks(
					clientId,
					createBlocksFromInnerBlocksTemplate( getTemplateRef.current( data, deletedElementsRef.current ) )
				);
			}
		};

		fetchProfile( trimmedEmail );
	}, [ avatarUrlSizeParam, clientId, defaultAvatarSize, replaceInnerBlocks, userEmail ] );

	// Reset the block items from the navigation menu when the API status changes.
	useEffect( () => {
		if ( apiStatus !== 'success' ) {
			replaceInnerBlocks(
				clientId,
				createBlocksFromInnerBlocksTemplate( getTemplateRef.current( placeholderProfile, deletedElements ) )
			);
		}
	}, [ apiStatus, clientId, deletedElements, placeholderProfile, replaceInnerBlocks ] );

	function handleUserTypeChange( type: UserTypes ) {
		let email = '';

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
						<>
							<TextControl
								type="email"
								value={ emailInputVal }
								onChange={ ( email ) => {
									setEmailInputVal( email );
									debouncedSetUserEmail( email );
								} }
								placeholder={ __( 'Enter email', 'gravatar-enhanced' ) }
							/>
							{ errorCode === 404 && (
								<a
									href={
										`mailto:${ emailInputVal }` +
										`?subject=${ encodeURIComponent(
											__( 'Let’s set up your Gravatar profile', 'gravatar-enhanced' )
										) }` +
										`&body=${ encodeURIComponent(
											sprintf(
												// translators: %1$s = newline
												__(
													'Hi there,%1$s%1$sI use Gravatar to create and manage a unified online profile — it’s free, fast to set up, and keeps your presence consistent wherever you interact online.%1$s%1$sSet up your Gravatar profile now: https://gravatar.com%1$s%1$sCheers,',
													'gravatar-enhanced'
												),
												'\n'
											)
										) }`
									}
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __( 'Invite to join Gravatar', 'gravatar-enhanced' ) }
								</a>
							) }
						</>
					) }
				</PanelBody>
			</InspectorControls>
			<div
				{ ...blockProps }
				className={ clsx( 'gravatar-block', layoutClassName, blockProps.className, {
					'gravatar-block--custom-text-color': !! blockProps.style.color,
					// Disable the click event of child blocks in some cases. So, the main block can be selected easily.
					'gravatar-block--child-block-unclickable':
						( userType === UserTypes.EMAIL && ! userEmail ) || ! isChildBlockClickable,
				} ) }
			>
				{ apiStatus === 'error' && <div className="gravatar-block__status">{ errorMsg }</div> }
				{ apiStatus === 'success' && <InnerBlocks allowedBlocks={ [] } renderAppender={ undefined } /> }
				{ apiStatus !== 'success' && (
					<InnerBlocks
						allowedBlocks={ [] }
						renderAppender={ undefined }
						templateLock="all"
						template={ getTemplateRef.current( placeholderProfile, deletedElements ) }
					/>
				) }
			</div>
		</>
	);
}
