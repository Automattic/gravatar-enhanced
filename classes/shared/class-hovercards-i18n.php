<?php

namespace Automattic\Gravatar\GravatarEnhanced\Shared;

class HovercardsI18n {
	/**
	 * Returns the i18n translations for the @gravatar-com/hovercards library.
	 *
	 * Keys must match the English strings used as lookup keys in the library's __t() function.
	 *
	 * @return array<string, string>
	 */
	public static function get_translations() {
		return [
			'View profile →'      => __( 'View profile →', 'gravatar-enhanced' ),
			'Edit your profile →' => __( 'Edit your profile →', 'gravatar-enhanced' ),
			'Contact'             => __( 'Contact', 'gravatar-enhanced' ),
			'Send money'          => __( 'Send money', 'gravatar-enhanced' ),
			'Email'               => __( 'Email', 'gravatar-enhanced' ),
			'Home Phone'          => __( 'Home Phone', 'gravatar-enhanced' ),
			'Work Phone'          => __( 'Work Phone', 'gravatar-enhanced' ),
			'Cell Phone'          => __( 'Cell Phone', 'gravatar-enhanced' ),
			'Contact Form'        => __( 'Contact Form', 'gravatar-enhanced' ),
			'Calendar'            => __( 'Calendar', 'gravatar-enhanced' ),
			'Sorry, we are unable to load this Gravatar profile.' => __( 'Sorry, we are unable to load this Gravatar profile.', 'gravatar-enhanced' ),
			'Gravatar not found.' => __( 'Gravatar not found.', 'gravatar-enhanced' ),
			'Too Many Requests.'  => __( 'Too Many Requests.', 'gravatar-enhanced' ),
			'Internal Server Error.' => __( 'Internal Server Error.', 'gravatar-enhanced' ),
			'Is this you?'        => __( 'Is this you?', 'gravatar-enhanced' ),
			'Claim your free profile.' => __( 'Claim your free profile.', 'gravatar-enhanced' ),
		];
	}
}
