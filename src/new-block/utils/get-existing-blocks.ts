/**
 * External dependencies
 */
import type { BlockInstance } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { BlockNames } from '../main/edit';

export type AttrNames = string[];

export default function getExistingBlocks( blocks: BlockInstance[] = [], attrNames: AttrNames = [] ): AttrNames {
	blocks.forEach( ( { name, innerBlocks, attributes } ) => {
		const isEmptyCol = name === BlockNames.COLUMN && ! innerBlocks?.length;

		if ( attributes.name && ! isEmptyCol ) {
			attrNames.push( attributes.name );
		}

		getExistingBlocks( innerBlocks, attrNames );
	} );

	return attrNames;
}
