import type { BlockDeprecation } from '@wordpress/blocks';

interface NewAttrs {
	userType: string;
	userEmail: string;
	deletedElements: Record< string, boolean >;
}

interface OldAttrs {
	userType: string;
	userValue: string;
}

const deprecation: BlockDeprecation< NewAttrs, OldAttrs > = {
	attributes: {
		userType: {
			type: 'string',
			default: 'author',
		},
		userValue: {
			type: 'string',
			default: '',
		},
	},
	isEligible: ( { userValue } ) => !! userValue?.trim(),
	migrate: ( { userType, userValue } ) => ( { userType, userEmail: userValue, deletedElements: {} } ),
	save: () => null,
};

export default deprecation;
