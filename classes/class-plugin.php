<?php

namespace Automattic\Gravatar\GravatarEnhanced;

require_once __DIR__ . '/class-module.php';
require_once __DIR__ . '/options/class-discussions.php';
require_once __DIR__ . '/email/class-email.php';
require_once __DIR__ . '/hovercards/class-hovercards.php';
require_once __DIR__ . '/avatar/class-avatar.php';
require_once __DIR__ . '/proxy/class-proxy.php';
require_once __DIR__ . '/quick-editor/class-quick-editor.php';
require_once __DIR__ . '/analytics/class-analytics.php';
require_once __DIR__ . '/block/class-block.php';
require_once __DIR__ . '/patterns/class-patterns.php';
require_once __DIR__ . '/woocommerce/class-admin-customers.php';
require_once __DIR__ . '/woocommerce/class-my-account.php';
require_once __DIR__ . '/oembed/class-oembed.php';
require_once __DIR__ . '/comments/class-comments.php';

class Plugin {
	const OPTION_NAME_AUTO = 'gravatar_enhanced_options';
	const OPTION_NAME_LAZY = 'gravatar_enhanced_options_lazy';

	/**
	 * @var Options\SavedOptions
	 */
	private $auto_options;

	/**
	 * @var Options\SavedOptions
	 */
	private $lazy_options;

	/**
	 * @var Email\EmailNotification
	 */
	private $email;

	/**
	 * @var Hovercards\Hovercards
	 */
	private $hovercards;

	/**
	 * @var Avatar\Avatar
	 */
	private $avatar;

	/**
	 * @var Proxy\Proxy
	 */
	private $proxy;

	/**
	 * @var QuickEditor\QuickEditor
	 */
	private $quick_editor;

	/**
	 * @var Analytics\Analytics
	 */
	private $analytics;

	/**
	 * @var Block\Block
	 */
	private $block;

	/**
	 * @var Patterns\Patterns
	 */
	private $patterns;

	/**
	 * @var Options\DiscussionsPage
	 */
	private $discussions;

	/**
	 * @var Woocommerce\AdminCustomers
	 */
	private $wc_admin_customers;

	/**
	 * @var Woocommerce\MyAccount
	 */
	private $wc_my_account;

	/**
	 * @var OEmbed\OEmbed
	 */
	private $oembed;

	/**
	 * @var Comments\Comments
	 */
	private $comments;

	/**
	 * @var Module[]
	 */
	private $modules;

	public function __construct() {
		$this->auto_options = new Options\SavedOptions( self::OPTION_NAME_AUTO, true );
		$this->lazy_options = new Options\SavedOptions( self::OPTION_NAME_LAZY, false );

		// Migrate old options. We're only interested in email settings. This will only run once.
		$migrator = new Options\Migrate();
		$migrator->maybe_migrate( $this->lazy_options, self::OPTION_NAME_LAZY );

		// Modules
		$this->email = new Email\EmailNotification( new Email\Preferences( $this->lazy_options ) );
		$this->hovercards = new Hovercards\Hovercards();
		$this->avatar = new Avatar\Avatar( new Avatar\Preferences( $this->auto_options ) );
		$this->proxy = new Proxy\Proxy( new Proxy\Preferences( $this->auto_options ) );
		$this->quick_editor = new QuickEditor\QuickEditor();
		$this->analytics = new Analytics\Analytics( new Analytics\Preferences( $this->auto_options ) );
		$this->block = new Block\Block();
		$this->patterns = new Patterns\Patterns();
		$this->wc_admin_customers = new Woocommerce\AdminCustomers();
		$this->wc_my_account = new Woocommerce\MyAccount();
		$this->oembed = new OEmbed\OEmbed();
		$this->comments = new Comments\Comments( new Comments\Preferences( $this->auto_options ) );

		// Collect all modules and filter them based on the whitelist if available.
		$this->modules = [
			'email' => $this->email,
			'hovercards' => $this->hovercards,
			'avatar' => $this->avatar,
			'proxy' => $this->proxy,
			'quick_editor' => $this->quick_editor,
			'analytics' => $this->analytics,
			'block' => $this->block,
			'patterns' => $this->patterns,
			'wc_admin_customers' => $this->wc_admin_customers,
			'wc_my_account' => $this->wc_my_account,
			'oembed' => $this->oembed,
			'comments' => $this->comments,
		];

		$modules_whitelist = apply_filters( 'gravatar_enhanced_modules_whitelist', null );
		if ( is_array( $modules_whitelist ) ) {
			$this->modules = array_intersect_key( $this->modules, array_flip( $modules_whitelist ) );
		}

		// Handles the discussions settings page
		$this->discussions = new Options\DiscussionsPage( $this->auto_options, $this->lazy_options, array_keys( $this->modules ) );

		// Ensure the options always exist. We don't need data saved in it as this is provided by the defaults
		if ( get_option( self::OPTION_NAME_AUTO, null ) === null ) {
			$this->auto_options->save();
		}
	}

	/**
	 * Start the plugin by initializing all the enabled modules.
	 *
	 * @return void
	 */
	public function init() {
		foreach ( $this->modules as $module ) {
			$module->init();
		}

		// Initialize discussions settings.
		$this->discussions->init();
	}

	/**
	 * Uninstall the plugin
	 *
	 * @return void
	 */
	public function uninstall() {

		// Uninstall all enabled modules.
		foreach ( $this->modules as $module ) {
			$module->uninstall();
		}

		// Uninstall discussion settings options.
		$this->auto_options->uninstall();
		$this->lazy_options->uninstall();

		// Just in case, flush the cache
		wp_cache_flush();
	}
}
