export default function trackEvent( name: string, options = undefined ) {
	if ( ! window?.gravatar?.recordTrackEvent ) {
		return;
	}

	window.gravatar.recordTrackEvent( name, options );
}
