import { BlockNames } from '../shared-types';
import type { BlockInstance } from '@wordpress/blocks';

export default function getExistingBlocks( blocks: BlockInstance[] = [], names: string[] = [] ): string[] {
	blocks.forEach( ( { name, innerBlocks, attributes } ) => {
		const isEmptyCol = name === BlockNames.COLUMN && ! innerBlocks?.length;

		if ( attributes.name && ! isEmptyCol ) {
			names.push( attributes.name );
		}

		getExistingBlocks( innerBlocks, names );
	} );

	return names;
}
