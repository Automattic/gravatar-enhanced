import type { BlockInstance } from '@wordpress/blocks';
import { BlockNames } from '../shared-types';

export default function getExistingBlocks( blocks: BlockInstance[] = [], names: string[] = [] ): string[] {
	blocks.forEach( ( { name, innerBlocks, attributes } ) => {
		const isEmptyGroup = name === BlockNames.GROUP && ! innerBlocks?.length;

		if ( attributes.name && ! isEmptyGroup ) {
			names.push( attributes.name );
		}

		getExistingBlocks( innerBlocks, names );
	} );

	return names;
}
