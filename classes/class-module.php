<?php

namespace Automattic\Gravatar\GravatarEnhanced;

interface Module {

	/**
	 * Initialize the module.
	 *
	 * @return void
	 */
	public function init();

	/**
	 * Uninstall the module.
	 *
	 * @return void
	 */
	public function uninstall();
}
