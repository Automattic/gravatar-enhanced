import './style.scss';

interface FediverseProfile {
	display_name: string;
	summary: string;
	avatar_url: string;
	profile_url: string;
	follow_url: string;
}

interface FediverseCardElements {
	wrapper: HTMLElement;
	name: HTMLElement;
	summary: HTMLElement;
	avatar: HTMLImageElement;
	link: HTMLAnchorElement;
}

const CARD_CLASS = 'gravatar-fediverse-card';
const CARD_VISIBLE_CLASS = 'gravatar-fediverse-card--visible';
const AVATAR_SELECTOR = 'img.avatar';
const AUTHOR_LINK_SELECTOR = 'a.url';
const REST_NAMESPACE = 'gravatar-enhanced/v1';
const REST_ROUTE = '/fediverse/lookup';

let card: FediverseCardElements | null = null;
let hideTimeout: ReturnType< typeof setTimeout > | null = null;

document.addEventListener( 'DOMContentLoaded', () => {
	if ( ! isFediverseHovercardsEnabled() ) {
		return;
	}

	createCard();
	attachListeners();
} );

function isFediverseHovercardsEnabled(): boolean {
	const settings = ( window as unknown as { gravatarEnhancedFediverse?: { enabled: boolean } } ).gravatarEnhancedFediverse;
	return settings?.enabled !== false;
}

function createCard(): void {
	const wrapper = document.createElement( 'div' );
	wrapper.className = CARD_CLASS;
	wrapper.setAttribute( 'role', 'tooltip' );
	wrapper.setAttribute( 'aria-hidden', 'true' );

	const avatar = document.createElement( 'img' );
	avatar.className = `${ CARD_CLASS }__avatar`;
	avatar.alt = '';
	avatar.width = 60;
	avatar.height = 60;

	const content = document.createElement( 'div' );
	content.className = `${ CARD_CLASS }__content`;

	const name = document.createElement( 'strong' );
	name.className = `${ CARD_CLASS }__name`;

	const summary = document.createElement( 'p' );
	summary.className = `${ CARD_CLASS }__summary`;

	const link = document.createElement( 'a' );
	link.className = `${ CARD_CLASS }__link`;
	link.textContent = __( 'View profile →', 'gravatar-enhanced' );
	link.setAttribute( 'rel', 'noopener noreferrer' );
	link.setAttribute( 'target', '_blank' );

	content.appendChild( name );
	content.appendChild( summary );
	content.appendChild( link );

	wrapper.appendChild( avatar );
	wrapper.appendChild( content );
	document.body.appendChild( wrapper );

	card = { wrapper, name, summary, avatar, link };
}

function attachListeners(): void {
	document.addEventListener( 'mouseover', handleMouseOver, true );
	document.addEventListener( 'mouseout', handleMouseOut, true );
}

function handleMouseOver( event: MouseEvent ): void {
	const target = event.target as HTMLElement;
	const avatar = target.closest( `${ AVATAR_SELECTOR }, .avatar` ) as HTMLImageElement | null;

	if ( ! avatar ) {
		return;
	}

	const commentEl = avatar.closest( '.comment-body, .comment-content, [class*="comment"]' );
	if ( ! commentEl ) {
		return;
	}

	const authorLink = commentEl.querySelector( AUTHOR_LINK_SELECTOR ) as HTMLAnchorElement | null;
	if ( ! authorLink ) {
		return;
	}

	const url = authorLink.href;
	if ( ! url || ! isFediverseUrl( url ) ) {
		return;
	}

	showCard( avatar, url );
}

function handleMouseOut( event: MouseEvent ): void {
	const target = event.target as HTMLElement;
	const avatar = target.closest( `${ AVATAR_SELECTOR }, .avatar` ) as HTMLImageElement | null;

	if ( ! avatar ) {
		return;
	}

	scheduleHideCard();
}

function isFediverseUrl( url: string ): boolean {
	try {
		const parsed = new URL( url );
		if ( ! parsed.host || parsed.host.includes( 'gravatar.com' ) ) {
			return false;
		}
		if ( parsed.protocol !== 'http:' && parsed.protocol !== 'https:' ) {
			return false;
		}
		return true;
	} catch {
		return false;
	}
}

async function showCard( avatar: HTMLImageElement, url: string ): void {
	if ( ! card ) {
		return;
	}

	clearHideTimeout();
	clearShowTimeout();

	card.wrapper.setAttribute( 'aria-hidden', 'true' );
	card.wrapper.classList.remove( CARD_VISIBLE_CLASS );

	positionCard( avatar );
	showLoadingState();

	try {
		const profile = await fetchProfile( url );
		if ( ! profile ) {
			hideCard();
			return;
		}

		renderProfile( profile );
		card.wrapper.setAttribute( 'aria-hidden', 'false' );
		card.wrapper.classList.add( CARD_VISIBLE_CLASS );
	} catch {
		hideCard();
	}
}

function showLoadingState(): void {
	if ( ! card ) {
		return;
	}

	card.avatar.style.opacity = '0.3';
	card.name.textContent = '';
	card.summary.textContent = '';
	card.link.href = '';
	card.link.style.display = 'none';
}

function renderProfile( profile: FediverseProfile ): void {
	if ( ! card ) {
		return;
	}

	card.avatar.style.opacity = '1';

	if ( profile.avatar_url ) {
		card.avatar.src = profile.avatar_url;
		card.avatar.style.display = 'block';
	} else {
		card.avatar.style.display = 'none';
	}

	card.name.textContent = profile.display_name || '';
	card.summary.textContent = profile.summary || '';

	if ( profile.profile_url ) {
		card.link.href = profile.profile_url;
		card.link.style.display = 'inline';
	} else {
		card.link.style.display = 'none';
	}
}

function hideCard(): void {
	clearHideTimeout();
	clearShowTimeout();

	if ( ! card ) {
		return;
	}

	card.wrapper.classList.remove( CARD_VISIBLE_CLASS );
	card.wrapper.setAttribute( 'aria-hidden', 'true' );
}

function scheduleHideCard(): void {
	clearHideTimeout();
	hideTimeout = setTimeout( hideCard, 150 );
}

let showTimeout: ReturnType< typeof setTimeout > | null = null;

function clearHideTimeout(): void {
	if ( hideTimeout ) {
		clearTimeout( hideTimeout );
		hideTimeout = null;
	}
}

function clearShowTimeout(): void {
	if ( showTimeout ) {
		clearTimeout( showTimeout );
		showTimeout = null;
	}
}

function positionCard( avatar: HTMLImageElement ): void {
	if ( ! card ) {
		return;
	}

	const rect = avatar.getBoundingClientRect();
	const cardWidth = 280;
	const cardHeight = 120;
	const offset = 8;

	let top = rect.top + window.scrollY - cardHeight - offset;
	let left = rect.left + window.scrollX + rect.width / 2 - cardWidth / 2;

	const viewportWidth = window.innerWidth;
	if ( left < 8 ) {
		left = 8;
	} else if ( left + cardWidth > viewportWidth - 8 ) {
		left = viewportWidth - cardWidth - 8;
	}

	if ( top < window.scrollY + 8 ) {
		top = rect.bottom + window.scrollY + offset;
	}

	card.wrapper.style.top = `${ top }px`;
	card.wrapper.style.left = `${ left }px`;
}

async function fetchProfile( url: string ): Promise< FediverseProfile | null > {
	const settings = ( window as unknown as { gravatarEnhancedFediverse?: { restUrl?: string } } ).gravatarEnhancedFediverse;
	const baseUrl = settings?.restUrl || '/wp-json';

	const apiUrl = `${ baseUrl }${ REST_NAMESPACE }${ REST_ROUTE }?url=${ encodeURIComponent( url ) }`;

	const response = await fetch( apiUrl, {
		headers: {
			Accept: 'application/json',
		},
	} );

	if ( ! response.ok ) {
		return null;
	}

	return response.json();
}

function __( text: string ): string {
	return text;
}
