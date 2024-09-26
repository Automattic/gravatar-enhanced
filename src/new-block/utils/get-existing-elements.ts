/**
 * External dependencies
 */
import type { BlockInstance } from '@wordpress/blocks';

export default function getExistingElements< T = Record< string, boolean > >(
	blocks: BlockInstance[] = [],
	names: T = {} as T
): T {
	blocks.forEach( ( { name, innerBlocks, attributes } ) => {
		const isEmptyCol = name === 'gravatar/block-column' && ! innerBlocks?.length;

		if ( attributes.name && ! isEmptyCol ) {
			names[ attributes.name ] = true;
		}

		getExistingElements( innerBlocks, names );
	} );

	return names;
}
