<?php

namespace Automattic\Gravatar\GravatarEnhanced;

require_once __DIR__ . '/options/class-discussions.php';
require_once __DIR__ . '/email/class-email.php';
require_once __DIR__ . '/hovercards/class-hovercards.php';
require_once __DIR__ . '/avatar/class-avatar.php';
require_once __DIR__ . '/proxy/class-proxy.php';
require_once __DIR__ . '/quick-editor/class-quick-editor.php';
require_once __DIR__ . '/analytics/class-analytics.php';
require_once __DIR__ . '/block/class-block.php';
require_once __DIR__ . '/block/class-new-block.php';
require_once __DIR__ . '/woocommerce/class-my-account.php';

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
	 * @var Block\NewBlock
	 */
	private $new_block;

	/**
	 * @var Options\DiscussionsPage
	 */
	private $discussions;

	/**
	 * @var Woocommerce\MyAccount
	 */
	private $wc_my_account;

	public function __construct() {
		$this->auto_options = new Options\SavedOptions( self::OPTION_NAME_AUTO, true );
		$this->lazy_options = new Options\SavedOptions( self::OPTION_NAME_LAZY, false );

		// Migrate old options. We're only interested in email settings. This will only run once.
		$migrator = new Options\Migrate();
		$migrator->maybe_migrate( $this->lazy_options, self::OPTION_NAME_LAZY );

		// Handles the discussions settings page
		$this->discussions = new Options\DiscussionsPage( $this->auto_options, $this->lazy_options );

		// Modules
		$this->email = new Email\EmailNotification( new Email\Preferences( $this->lazy_options ) );
		$this->hovercards = new Hovercards\Hovercards();
		$this->avatar = new Avatar\Avatar( new Avatar\Preferences( $this->auto_options ) );
		$this->proxy = new Proxy\Proxy( new Proxy\Preferences( $this->auto_options ) );
		$this->quick_editor = new QuickEditor\QuickEditor();
		$this->analytics = new Analytics\Analytics( new Analytics\Preferences( $this->auto_options ) );
		$this->block = new Block\Block();
		$this->new_block = new Block\NewBlock();
		$this->wc_my_account = new Woocommerce\MyAccount();

		// Ensure the options always exist. We don't need data saved in it as this is provided by the defaults
		if ( get_option( self::OPTION_NAME_AUTO, null ) === null ) {
			$this->auto_options->save();
		}
	}

	/**
	 * Start the plugin
	 *
	 * @return void
	 */
	public function init() {
		$this->email->init();
		$this->hovercards->init();
		$this->avatar->init();
		$this->proxy->init();
		$this->quick_editor->init();
		$this->discussions->init();
		$this->analytics->init();
		$this->block->init();
<<<<<<< HEAD
		$this->new_block->init();
=======
		$this->wc_my_account->init();
>>>>>>> trunk
	}

	/**
	 * Uninstall the plugin
	 *
	 * @return void
	 */
	public function uninstall() {
		$this->email->uninstall();
		$this->hovercards->uninstall();
		$this->auto_options->uninstall();
		$this->lazy_options->uninstall();

		// Just in case, flush the cache
		wp_cache_flush();
	}
}
