import type { MaybeLinkProps } from "../shared-types";

type Props = MaybeLinkProps;

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
