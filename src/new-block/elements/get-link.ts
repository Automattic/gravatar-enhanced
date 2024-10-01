/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import type { Attrs } from '../link/edit';

export type Props = Attrs;

export default function getLink( { linkUrl, text, className, color }: Props ): string {
	const style = color ? `style="color: ${ color };"` : '';
	const stroke = color ? `stroke="${ color }"` : '';

	return `
		<a
			class="${ clsx( 'gravatar-block-link', className ) }"
			${ style }
			href="${ linkUrl }"
			target="_blank"
			rel="noreferrer"
		>
			<span>${ text }</span>
			<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
				<path
					${ stroke }
					d="M12.6667 8.33338L9.16666 12.1667M12.6667 8.33338L2.66666 8.33338M12.6667 8.33338L9.16666 4.83338"
					strokeWidth="1.5"
				></path>
			</svg>
		</a>
	`;
}
