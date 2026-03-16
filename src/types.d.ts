import { select } from '@wordpress/data';

interface GravatarAPIAccount {
	url: string;
	service_label: string;
	service_icon: string;
	service_type: string;
	is_hidden: boolean;
}

declare global {
	type HovercardsI18n = Record<string, string>;

	interface GravatarEnhancedComments {
		locale: string;
		email?: string;
		hovercardsI18n?: HovercardsI18n;
	}

	interface GravatarAPIProfile {
		hash: string;
		display_name: string;
		description: string;
		profile_url: string;
		avatar_url: string;
		avatar_alt_text: string;
		location: string;
		job_title: string;
		company: string;
		pronunciation: string;
		pronouns: string;
		verified_accounts: GravatarAPIAccount[];
		background_color: string;
		header_image: string;
	}

	interface QuickEditorText {
		createButton: string;
		updateButton: string;
		viewButton: string;
		errorTitle: string;
		errorDescription: string;
		unknownTitle: string;
		unknownDescription: string;
		otherUnknownTitle: string;
		otherUnknownDescription: string;
	}

	interface QuickEditor {
		locale: string;
		email: string;
		hash: string;
		text: QuickEditorText;
		avatar: string;
		canEdit: boolean;
		hovercardsI18n?: HovercardsI18n;
	}

	var geQuickEditor: QuickEditor;
	var geWcMyAccount: QuickEditor;
	var gravatar: {
		recordTrackEvent: ( name: string, options?: any ) => void;
	};

	var gravatarEnhancedComments: GravatarEnhancedComments;
	var gravatarEnhancedHovercardsI18n: HovercardsI18n | undefined;
	var gravatarEnhancedWcAdminCustomersI18n: HovercardsI18n | undefined;

	type SelectFn = typeof select;
}
