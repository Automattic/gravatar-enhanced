/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect, useState, useCallback, useRef } from '@wordpress/element';
import { useEntityProp } from '@wordpress/core-data';

/**
 * External dependencies
 */
import debounce from 'lodash.debounce';
import { sha256 } from 'js-sha256';

/**
 * Internal dependencies
 */
import { attachInlineHovercard } from '../hovercards/utils';

const TYPE_OPTIONS = {
	GRAVATAR: 'gravatar',
	HOVERCARD: 'hovercard',
};

const USER_TYPE_OPTIONS = {
	AUTHOR: 'author',
	USER: 'user',
	EMAIL: 'email',
};

/**
 * Return the author of the post taking into account its context.
 * - Returns the post author in a query loop (provided by context's postType and postId).
 * - Returns the current post/page author elsewhere.
 * - Returns null if not available (e.g. when editing a template for the first time, since there is no author yet).
 * - Returns null if in Full Site Editor outside of a query loop.
 *
 * @param {string} postType
 * @param {number} postId
 * @param {string} queryId
 * @returns
 */
const useAuthor = ( postType, postId, queryId ) => {
	const isFullSiteEditing = useSelect( ( select ) => !! select( 'core/edit-site' ), [] );
	const isInQueryLoop = Number.isFinite( queryId );

	const [ authorId ] = useEntityProp( 'postType', postType, 'author', postId );

	// If in FSE and not in a query loop, return null
	// This is because the author for a template is not set initially until the template is modified.
	// And also, getting this author from the `render.php` is not possible, so we generate an inconsistency between the editor and the render.
	if ( isFullSiteEditing && ! isInQueryLoop ) {
		return null;
	}

	return useSelect(
		( select ) => {
			return authorId ? select( 'core' ).getUser( authorId ) : null;
		},
		[ authorId ]
	);
};

/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes, context: { postId, postType, queryId } } ) {
	const { type, userType, userValue, size } = attributes;

	const [ email, setEmail ] = useState( null );
	const allUsers = useSelect( ( select ) => select( 'core' ).getUsers(), [] );
	const authorUser = useAuthor( postType, postId, queryId );
	const selectedUser = useSelect(
		( select ) => {
			if ( userType === USER_TYPE_OPTIONS.USER && userValue ) {
				return select( 'core' ).getUser( userValue );
			}
			return null;
		},
		[ userType, userValue ]
	);
	const hovercardContainerRef = useRef( null );

	const debouncedSetEmail = useCallback(
		debounce( ( newEmail ) => setEmail( newEmail ), 500 ),
		[]
	);

	useEffect( () => {
		if ( type === TYPE_OPTIONS.HOVERCARD && email !== null ) {
			attachInlineHovercard( hovercardContainerRef.current, sha256( email ) );
		}
	}, [ type, email ] );

	// Update the email address when the user type or value changes.
	useEffect( () => {
		if ( userType === USER_TYPE_OPTIONS.EMAIL ) {
			debouncedSetEmail( userValue );
		} else if ( userType === USER_TYPE_OPTIONS.USER && selectedUser ) {
			setEmail( selectedUser.email );
		} else if ( userType === USER_TYPE_OPTIONS.AUTHOR && authorUser ) {
			setEmail( authorUser.email );
		}
	}, [ userType, userValue, authorUser, selectedUser ] );

	const [ imageSize, setImageSize ] = useState( size );

	// Autoselect first user in the list when userType is set to 'user' and no userValue is set.
	useEffect( () => {
		if ( userType === USER_TYPE_OPTIONS.USER && allUsers && allUsers.length && ! userValue ) {
			setAttributes( { userValue: allUsers[ 0 ].id.toString() } );
		}
	}, [ userType, allUsers, userValue ] );

	// If the author user does not exist, set the user type to 'user'.
	useEffect( () => {
		if ( userType === USER_TYPE_OPTIONS.AUTHOR && authorUser === null ) {
			setAttributes( { userType: USER_TYPE_OPTIONS.USER, userValue: '' } );
		}
	}, [ userType, authorUser ] );

	const allUsersOptions = allUsers
		? allUsers.map( ( user ) => ( { label: `${ user.name } (${ user.nickname })`, value: user.id } ) )
		: [];

	const debouncedSetImageSize = useCallback(
		debounce( ( newSize ) => setImageSize( newSize ), 500 ),
		[]
	);

	const onSizeChange = ( newSize ) => {
		// Update the size in the attribute and block immediately.
		setAttributes( { size: newSize } );
		// Update the size of the source image after debouncing to avoid too many requests.
		debouncedSetImageSize( newSize );
	};

	// User type options – author only available if the author user exists
	const userTypeOptions = [
		{ label: __( 'User', 'gravatar-enhanced' ), value: USER_TYPE_OPTIONS.USER },
		{ label: __( 'Custom email', 'gravatar-enhanced' ), value: USER_TYPE_OPTIONS.EMAIL },
	];
	if ( authorUser ) {
		userTypeOptions.unshift( { label: __( 'Author', 'gravatar-enhanced' ), value: USER_TYPE_OPTIONS.AUTHOR } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'gravatar-enhanced' ) }>
					<p>{ __( 'Display', 'gravatar-enhanced' ) }</p>
					<SelectControl
						value={ type }
						options={ [
							{ label: __( 'Avatar', 'gravatar-enhanced' ), value: TYPE_OPTIONS.GRAVATAR },
							{ label: __( 'Gravatar Card', 'gravatar-enhanced' ), value: TYPE_OPTIONS.HOVERCARD },
						] }
						onChange={ ( newType ) => setAttributes( { type: newType } ) }
					/>
					{ type === TYPE_OPTIONS.GRAVATAR && (
						<RangeControl
							label={ __( 'Size', 'gravatar-enhanced' ) }
							value={ size }
							onChange={ onSizeChange }
							min={ 20 }
							max={ 2048 }
						/>
					) }
					<SelectControl
						label={ __( 'Select User', 'gravatar-enhanced' ) }
						value={ userType }
						options={ userTypeOptions }
						onChange={ ( newType ) => setAttributes( { userType: newType, userValue: '' } ) }
					/>
					{ userType === USER_TYPE_OPTIONS.USER && (
						<SelectControl
							value={ userValue }
							options={ allUsersOptions }
							onChange={ ( newValue ) => setAttributes( { userValue: newValue } ) }
						/>
					) }
					{ userType === USER_TYPE_OPTIONS.EMAIL && (
						<TextControl
							value={ userValue }
							onChange={ ( newValue ) => setAttributes( { userValue: newValue } ) }
							type="email"
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				{ type === TYPE_OPTIONS.HOVERCARD && <div ref={ hovercardContainerRef } /> }
				{ type === TYPE_OPTIONS.GRAVATAR && email !== null && (
					<img
						src={ `https://gravatar.com/avatar/${ sha256( email ) }?s=${ imageSize }` }
						width={ size }
						height={ size }
						alt={ sprintf(
							__( "%s's avatar", 'gravatar-enhanced' ),
							selectedUser?.name ?? __( 'Unknown', 'gravatar-enhanced' )
						) }
					/>
				) }
			</div>
		</>
	);
}
