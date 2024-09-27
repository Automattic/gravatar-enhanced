import { sha256 } from 'js-sha256';
import { Hovercards } from '@gravatar-com/hovercards';
import './style-admin-customers.scss';
import '@gravatar-com/hovercards/dist/style.css';

/** Selector for the customer table */
const CUSTOMER_TABLE_SELECTOR = '.woocommerce-table__table:not(.is-loading) table';
/** Selector for the main content area */
const MAIN_CONTENT_SELECTOR = '.woocommerce-layout__main';
/** Selector for table rows excluding the header */
const TABLE_ROWS_SELECTOR = 'tr:not(:first-child)';
/** Selector for email links within table cells */
const EMAIL_CELL_SELECTOR = 'td a[href^="mailto:"]';

document.addEventListener( 'DOMContentLoaded', () => {
	const main = document.querySelector( MAIN_CONTENT_SELECTOR );

	if ( ! main ) {
		return;
	}

	const customerTableObserver = new MutationObserver( onCustomerTableChange );
	const mainContentObserver = new MutationObserver( onMainContentChanged );

	mainContentObserver.observe( main, { childList: true, subtree: true } );

	/**
	 * Callback function for main content mutation observer.
	 * Checks for the presence of the customer table and injects avatars when the table is loaded.
	 *
	 * @param {MutationRecord[]} mutationList - Array of mutation records.
	 * @return {void}
	 */
	function onMainContentChanged( mutationList ) {
		for ( const mutation of mutationList ) {
			if ( mutation.type === 'childList' ) {
				const table = main.querySelector( CUSTOMER_TABLE_SELECTOR );

				if ( ! table ) {
					return;
				}

				mainContentObserver.disconnect();
				customerTableObserver.disconnect();

				injectAvatars();
				return;
			}
		}
	}

	/**
	 * Callback function for customer table mutation observer.
	 * Re-injects avatars when changes are detected in the customer table.
	 *
	 * @return {void}
	 */
	function onCustomerTableChange() {
		mainContentObserver.disconnect();
		customerTableObserver.disconnect();
		injectAvatars();
	}

	/**
	 * Injects Gravatar avatars into the customer table and attaches hovercards.
	 *
	 * @return {void}
	 */
	function injectAvatars() {
		const hovercards = new Hovercards();
		const table = main.querySelector< HTMLTableElement >( CUSTOMER_TABLE_SELECTOR );

		if ( ! table ) {
			observeChanges( table );
			return;
		}

		const rows = table.querySelectorAll( TABLE_ROWS_SELECTOR );

		if ( ! rows.length ) {
			observeChanges( table );
			return;
		}

		rows.forEach( ( row ) => {
			const emailCell = row.querySelector( EMAIL_CELL_SELECTOR );

			if ( ! emailCell ) {
				return;
			}

			const cell = row.children[ 0 ];
			const wrapper = cell.querySelector( '.wc-customers-gravatar__wrapper' );

			const email = emailCell.getAttribute( 'href' ).replace( 'mailto:', '' );
			const avatarSrc = 'https://gravatar.com/avatar/' + sha256( email ) + '?s=32&d=mm';

			if ( wrapper ) {
				const avatar = wrapper.querySelector( 'img' );

				if ( avatarSrc === avatar.src ) {
					return;
				}

				wrapper.removeChild( avatar );

				const newAvatar = document.createElement( 'img' );
				newAvatar.src = avatarSrc;

				wrapper.insertBefore( newAvatar, wrapper.firstChild );
			} else {
				const avatar = document.createElement( 'img' );
				avatar.src = avatarSrc;

				const div = document.createElement( 'div' );
				div.classList.add( 'wc-customers-gravatar__wrapper' );
				div.appendChild( avatar );
				div.appendChild( cell.firstChild );

				cell.appendChild( div );
			}
		} );

		hovercards.attach( table );
		observeChanges( table );
	}

	/**
	 * Observes changes in the main content and customer table to re-inject avatars when necessary.
	 *
	 * @param {HTMLTableElement | null} table - The customer table element to observe.
	 * @return {void}
	 */
	function observeChanges( table ) {
		mainContentObserver.observe( main, { childList: true, subtree: true } );

		if ( table ) {
			customerTableObserver.observe( table, { childList: true, attributes: true, subtree: true } );
		}
	}
} );
