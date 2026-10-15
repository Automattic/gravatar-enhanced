import type { MaybeLinkProps } from '../shared-types';
import { escapeAttribute } from '@wordpress/escape-html';
import getSafeUrlAttribute from './get-safe-url-attribute';

interface Props extends MaybeLinkProps {
	children: string | string[];
}

export default function getMaybeLink( { linkUrl, children, ...rest }: Props ): string {
	const attrs = Object.entries( rest )
		.filter( ( [ , value ] ) => value !== undefined && value !== null && value !== false )
		.map( ( [ key, value ] ) => `${ key }="${ escapeAttribute( String( value ) ) }"` )
		.join( ' ' );
	children = Array.isArray( children ) ? children.join( '' ) : children;
	const safeLinkUrl = getSafeUrlAttribute( linkUrl, 'link' );

	return safeLinkUrl
		? `<a href="${ safeLinkUrl }" ${ attrs } target="_blank" rel="noopener noreferrer">${ children }</a>`
		: `<div ${ attrs }>${ children }</div>`;
}
