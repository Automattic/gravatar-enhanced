import type { InnerBlockTemplate } from '@wordpress/blocks';
import type { InnerBlockAttrsMap, MainEditAttrs } from '../shared-types';
import { BlockNames } from '../shared-types';

export default function getBlockTemplate< T extends BlockNames >(
	blockName: T,
	elemName: string,
	deletedElements: MainEditAttrs[ 'deletedElements' ],
	attrs: InnerBlockAttrsMap[ T ],
	innerBlocks?: T extends BlockNames.COLUMN ? InnerBlockTemplate[] : never
): InnerBlockTemplate | null {
	if ( deletedElements[ elemName ] ) {
		return null;
	}

	let filteredBlocks: InnerBlockTemplate[] = [];

	if ( Array.isArray( innerBlocks ) ) {
		filteredBlocks = innerBlocks.filter( Boolean );

		if ( ! filteredBlocks.length ) {
			return null;
		}
	}

	// Give the block a unique name for the deleted elements to work.
	return [ blockName, { name: elemName, ...attrs }, filteredBlocks ];
}
