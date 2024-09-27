interface GravatarAPIAccount {
	url: string;
	service_label: string;
	service_icon: string;
	service_type: string;
	is_hidden: boolean;
}

interface GravatarAPIProfile {
	hash: string;
	display_name: string;
	description: string;
	profile_url: string;
	location: string;
	job_title: string;
	company: string;
	verified_accounts: GravatarAPIAccount[];
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

declare interface QuickEditor {
	locale: string;
	email: string;
	hash: string;
	text: QuickEditorText;
	avatar: string;
	canEdit: boolean;
}

declare var geQuickEditor: QuickEditor;
declare var geWcMyAccount: QuickEditor;
declare var gravatar: {
	recordTrackEvent: ( name: string, options?: any ) => void;
};
