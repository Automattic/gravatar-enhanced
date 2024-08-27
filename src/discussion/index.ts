import trackEvent from '../shared/analytics';

document.addEventListener( 'DOMContentLoaded', () => {
	const form = document.querySelector( 'form[action="options.php"]' );

	if ( ! form ) {
		return;
	}

	form.addEventListener( 'submit', ( ev ) => {
		const formData = new FormData( ev.target as HTMLFormElement );
		const entries = Object.fromEntries( formData );
		const gravEntries = {
			hideReferrer: entries.gravatar_hide_referrer === '1' ? true : false,
			sendEmail: entries.gravatar_invitation_email === '1' ? true : false,
			forceDefault: entries.gravatar_force_default_avatar === '1' ? true : false,
			proxy: entries.gravatar_proxy === '1' ? true : false,
			proxyHash: entries?.gravatar_proxy_hash || '',
		}

		trackEvent( 'gravatar_enhanced_save_options', gravEntries );
	} );
} );