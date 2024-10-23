import { SVG, Path } from '@wordpress/components';

export default function ColumnIcon() {
	return (
		<SVG
			viewBox="0 0 24 24"
			xmlns="http://www.w3.org/2000/svg"
			width="24"
			height="24"
			aria-hidden="true"
			focusable="false"
		>
			<Path
				fillRule="evenodd"
				clipRule="evenodd"
				d="M15 7.5h-5v10h5v-10Zm1.5 0v10H19a.5.5 0 0 0 .5-.5V8a.5.5 0 0 0-.5-.5h-2.5ZM6 7.5h2.5v10H6a.5.5 0 0 1-.5-.5V8a.5.5 0 0 1 .5-.5ZM6 6h13a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
			></Path>
		</SVG>
	);
}
