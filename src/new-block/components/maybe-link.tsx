import type { ReactNode } from 'react';

export interface Props {
	linkUrl?: string;
	children: ReactNode;
	[ key: string ]: any;
}

export default function MaybeLink( { linkUrl, children, ...restProps }: Props ) {
	return linkUrl ? (
		// eslint-disable-next-line react/jsx-no-target-blank
		<a { ...restProps } href={ linkUrl } target="_blank">
			{ children }
		</a>
	) : (
		<div { ...restProps }>{ children }</div>
	);
}
