import type {
	InnerBlockAttrsMap,
	MainEditAttrs,
	GroupAttrs,
	ImageAttrs,
	LinkAttrs,
	NameAttrs,
	ParagraphAttrs,
} from '../shared-types';
import { BlockNames } from '../shared-types';
import { getGroup, getImage, getName, getParagraph, getLink } from '../view-elements';

export default function getViewElement< T extends BlockNames >(
	blockName: T,
	elemName: string,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	props: InnerBlockAttrsMap[ T ],
	children?: T extends BlockNames.GROUP ? string[] : never
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
		case BlockNames.GROUP:
			return getGroup( props as GroupAttrs, filteredChildren );
		case BlockNames.IMAGE:
			return getImage( props as ImageAttrs );
		case BlockNames.NAME:
			return getName( props as NameAttrs );
		case BlockNames.PARAGRAPH:
			return getParagraph( props as ParagraphAttrs );
		case BlockNames.LINK:
			return getLink( props as LinkAttrs );
		default:
			return '';
	}
}
