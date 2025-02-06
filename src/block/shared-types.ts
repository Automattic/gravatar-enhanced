import type { ReactNode } from 'react';

export enum UserTypes {
	AUTHOR = 'author',
	USER = 'user',
	EMAIL = 'email',
}

export enum BlockNames {
	COLUMN = 'gravatar/block-column',
	IMAGE = 'gravatar/block-image',
	NAME = 'gravatar/block-name',
	PARAGRAPH = 'gravatar/block-paragraph',
	LINK = 'gravatar/block-link',
}

export enum KnownElemNames {
	AVATAR = 'avatar',
	DISPLAY_NAME = 'displayName',
	JOB = 'job',
	COMPANY = 'company',
	LOCATION = 'location',
	DESCRIPTION = 'description',
	GRAVATAR = 'gravatar',
	VIEW_PROFILE = 'viewProfile',
	HEADER = 'header',
	JOB_COMPANY_LOCATION_WRAPPER = 'jobCompanyLocationWrapper',
	JOB_COMPANY_WRAPPER = 'jobCompanyWrapper',
	FOOTER = 'footer',
	VERIFIED_ACCOUNTS = 'verifiedAccounts',
}

export interface InnerBlockAttrsMap {
	[ BlockNames.COLUMN ]: ColumnAttrs;
	[ BlockNames.IMAGE ]: ImageAttrs;
	[ BlockNames.NAME ]: NameAttrs;
	[ BlockNames.PARAGRAPH ]: ParagraphAttrs;
	[ BlockNames.LINK ]: LinkAttrs;
}

export interface MainEditAttrs {
	layout: 'default' | 'portrait' | 'landscape' | 'line';
	avatarUrlSizeParam: number;
	userType: UserTypes;
	userEmail: string;
	deletedElements: Record< string, boolean >;
}

export type ColumnAttrs = Partial< {
	linkUrl: string;
	verticalAlignment: boolean;
	className: string;
} >;

export interface ImageAttrs {
	linkUrl?: string;
	imageUrl: string;
	imageWidth: number;
	imageHeight: number;
	imageAlt: string;
	className?: string;
}

export interface LinkAttrs {
	linkUrl: string;
	text: string;
	className?: string;
}

export interface NameAttrs {
	linkUrl?: string;
	text: string;
	className?: string;
}

export interface ParagraphAttrs {
	linkUrl?: string;
	text: string;
	className?: string;
}

export interface MaybeLinkProps {
	linkUrl?: string;
	children: ReactNode;
	[ key: string ]: any;
}
