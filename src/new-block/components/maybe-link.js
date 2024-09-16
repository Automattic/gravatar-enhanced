export default function MaybeLink( { linkUrl, children, ...restProps } ) {
	return linkUrl ? (
		<a { ...restProps } href={ linkUrl } target="_blank" rel="noreferrer">
			{ children }
		</a>
	) : (
		<div { ...restProps }>{ children }</div>
	);
}
