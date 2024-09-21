import type { ReactNode } from 'react';

interface Props {
	linkUrl?: string;
	children: ReactNode;
	[ key: string ]: any;
}

export default function MaybeLink( { linkUrl, children, ...restProps }: Props ) {
	return linkUrl ? (
		<a { ...restProps } href={ linkUrl } target="_blank" rel="noreferrer">
			{ children }
		</a>
	) : (
		<div { ...restProps }>{ children }</div>
	);
}
