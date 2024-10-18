import type { BlockInstance } from '@wordpress/blocks';
import { BlockNames } from '../edit';

export type Names = string[];

export default function getExistingBlocks( blocks: BlockInstance[] = [], names: Names = [] ): Names {
	blocks.forEach( ( { name, innerBlocks, attributes } ) => {
		const isEmptyCol = name === BlockNames.COLUMN && ! innerBlocks?.length;

		if ( attributes.name && ! isEmptyCol ) {
			names.push( attributes.name );
		}

		getExistingBlocks( innerBlocks, names );
	} );

	return names;
}
