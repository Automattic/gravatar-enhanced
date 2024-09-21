/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
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
 * Edit function for the block.
 *
 * This currently supports only the hovercard type.
 * Originally, it supported also an avatar type but was removed since didn't add any extra features to the official Avatar block.
 * 'type' option is kept for future extensibility.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes, context: { postId, postType, queryId } } ) {
	const { type, userType, userValue } = attributes;

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
				<div ref={ hovercardContainerRef } />
			</div>
		</>
	);
}
