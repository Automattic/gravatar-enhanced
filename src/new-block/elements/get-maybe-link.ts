import type { Props as BasedProps } from '../components/maybe-link';

interface Props extends BasedProps {
	children: string | string[];
}

export default function getMaybeLink( { linkUrl, children, ...rest }: Props ): string {
	const attrs = Object.entries( rest )
		.map( ( [ key, value ] ) => `${ key }="${ value }"` )
		.join( ' ' );
	children = Array.isArray( children ) ? children.join( '' ) : children;

	return linkUrl
		? `<a href="${ linkUrl }" ${ attrs } target="_blank">${ children }</a>`
		: `<div ${ attrs }>${ children }</div>`;
}
