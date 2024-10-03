/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import type { Attrs as EditAttrs, InnerBlockAttrsMap as PropsMap } from './edit';
import { BlockNames, KnownElemNames } from './edit';
import type { Props as ColumnProps } from './elements/get-column';
import type { Props as ImageProps } from './elements/get-image';
import type { Props as NameProps } from './elements/get-name';
import type { Props as ParagraphProps } from './elements/get-paragraph';
import type { Props as LinkProps } from './elements/get-link';
import { getColumn, getImage, getName, getParagraph, getLink } from './elements';
import { fetchProfile } from './utils';

interface Attrs {
	hashedEmail: string;
	deletedElements: Pick< EditAttrs, 'deletedElements' >;
}

document.addEventListener( 'DOMContentLoaded', () => {
	const gravatarBlocks = document.querySelectorAll< HTMLDivElement >( '.gravatar-block' );

	gravatarBlocks.forEach( async ( block ) => {
		block.innerHTML = __( 'Loading…', 'gravatar-enhanced' );

		if ( ! block.dataset.attrs ) {
			block.innerHTML = __( 'Oops! Something went wrong.', 'gravatar-enhanced' );
			return;
		}

		const { hashedEmail = '', deletedElements = {} } = JSON.parse( block.dataset.attrs ) as Attrs;

		const { error, data } = await fetchProfile( hashedEmail );

		if ( error ) {
			block.innerHTML = error;
			return;
		}

		function getElement< T extends BlockNames >(
			blockName: T,
			elemName: string,
			props: PropsMap[ T ],
			children?: T extends BlockNames.COLUMN ? string[] : never
		): string {
			if ( deletedElements[ elemName ] ) {
				return '';
			}

			let filteredChildren: string[] = [];

			if ( Array.isArray( children ) ) {
				filteredChildren = children.filter( Boolean );

				if ( ! filteredChildren.length ) {
					return '';
				}
			}

			switch ( blockName ) {
				case BlockNames.COLUMN:
					return getColumn( props as ColumnProps, filteredChildren );
				case BlockNames.IMAGE:
					return getImage( props as ImageProps );
				case BlockNames.NAME:
					return getName( props as NameProps );
				case BlockNames.PARAGRAPH:
					return getParagraph( props as ParagraphProps );
				case BlockNames.LINK:
					return getLink( props as LinkProps );
				default:
					return '';
			}
		}

		/* eslint-disable camelcase */
		// TODO: More templates to be added for different patterns.
		function getTemplate(): string {
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
			} = data;

			// TODO: Reuse these main UI elements to compose patterns.
			const avatar =
				avatar_url &&
				getElement( BlockNames.IMAGE, KnownElemNames.AVATAR, {
					linkUrl: profile_url,
					imageUrl: avatar_url,
					imageWidth: 72,
					imageHeight: 72,
					imageAlt: avatar_alt_text || display_name,
					className: 'gravatar-block-image--avatar',
				} );

			const displayName =
				display_name &&
				getElement( BlockNames.NAME, KnownElemNames.DISPLAY_NAME, {
					text: display_name,
					className: 'gravatar-block-text-truncate-2-lines',
					color: '#101517',
				} );

			const jobTitle =
				job_title &&
				getElement( BlockNames.PARAGRAPH, KnownElemNames.JOB, { text: job_title, color: '#50575E' } );

			const company =
				com && getElement( BlockNames.PARAGRAPH, KnownElemNames.COMPANY, { text: com, color: '#50575E' } );

			const location =
				loc &&
				getElement( BlockNames.PARAGRAPH, KnownElemNames.LOCATION, {
					className: 'gravatar-block-text-truncate-1-line',
					text: loc,
					color: '#50575E',
				} );

			const description =
				desc &&
				getElement( BlockNames.PARAGRAPH, KnownElemNames.DESCRIPTION, {
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
			const verifiedAccounts = verified_accounts.map(
				( { url, service_type, service_icon, service_label, is_hidden } ) =>
					! is_hidden &&
					getElement( BlockNames.IMAGE, service_type, {
						linkUrl: url,
						imageUrl: service_icon,
						imageWidth: 32,
						imageHeight: 32,
						imageAlt: service_label,
					} )
			);

			const viewProfile =
				profile_url &&
				getElement( BlockNames.LINK, KnownElemNames.VIEW_PROFILE, {
					linkUrl: profile_url,
					text: __( 'View profile', 'gravatar-enhanced' ),
					className: 'gravatar-block-link--align-right',
					color: '#50575E',
				} );

			return `
				${ getElement(
					BlockNames.COLUMN,
					KnownElemNames.HEADER,
					{ className: 'gravatar-block-column--header gravatar-block-column--align-center' },
					[
						avatar,
						getElement(
							BlockNames.COLUMN,
							KnownElemNames.JOB_COMPANY_LOCATION_WRAPPER,
							{ linkUrl: profile_url, verticalAlignment: true },
							[
								displayName,
								getElement(
									BlockNames.COLUMN,
									KnownElemNames.JOB_COMPANY_WRAPPER,
									{ className: 'gravatar-block-column--comma-separated' },
									[ jobTitle, company ]
								),
								location,
							]
						),
					]
				) }
				${ description }
				${ getElement(
					BlockNames.COLUMN,
					KnownElemNames.FOOTER,
					{ className: 'gravatar-block-column--footer gravatar-block-column--align-center' },
					[ ...verifiedAccounts, viewProfile ]
				) }
			`;
		}
		/* eslint-enable camelcase */

		block.innerHTML = getTemplate();
	} );
} );
