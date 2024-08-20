import './style.scss';
import checkUserProfile from './check-user-profile';

document.addEventListener( 'DOMContentLoaded', () => {
	if ( ! geQuickEditor ) {
		return;
	}

	checkUserProfile( geQuickEditor );
} );
