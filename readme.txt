=== Gravatar Enhanced - Avatars, Profiles, and Privacy ===
Contributors: automattic, batmoo, johnny5, aaronfc, wellyshen
Tags: avatar, profile, privacy, comments, profile picture
Tested up to: 6.6
Stable tag: 0.5.0
License: GPLv2

The official Gravatar plugin, featuring privacy-focused settings, easy profile updates, and customizable Gravatar Profile blocks.

== Description ==

Elevate your WordPress site with Gravatar Enhanced - the plugin that simplifies digital identity and improves user engagement.

Key Features:

- Privacy-focused settings
- Improved accessibility
- Easy profile updates
- Versatile Gravatar Profile block
- Commenter engagement tools
- WooCommerce Integration

=== Privacy Protection ===

Just by enabling the plugin, the plugin ensures that no referrer information is sent to Gravatar. The opt-in proxy service also keeps IP addresses from being exposed or logged.

=== Accessibility Improvements ===
All avatars now include alt-text, enhancing the experience for users with screen readers. Alt-text can be edited in the Gravatar Profile editor.

=== Simplified Profile Management ===
Visit Users > Your Profile to edit your Gravatar profile directly from WordPress. Preview your hovercard and make updates with ease.

=== Gravatar Profile Block ===
Add Gravatar profiles to posts, pages, author bios, or company pages using our custom block. Perfect for showcasing team members or guest contributors without needing to give them access to your WordPress website.

=== Boost Engagement ===
Automatically remind commenters without avatars to create a Gravatar, increasing visual engagement on your blog.

=== WooCommerce Integration ===
Enhance your WooCommerce store by displaying user Gravatar avatars on the My Account page. Customers can view and update their avatars directly from their account dashboard, improving personalization and user engagement.

- Seamless Integration: Automatically works if WooCommerce is installed and activated.
- Direct Avatar Management: Users can change their Gravatar avatars without leaving your site.
- Improved Personalization: Adds a personal touch to the shopping experience, fostering customer loyalty.

== Frequently Asked Questions ==
Q: Will this plugin impact site performance?
No, the plugin is optimized for efficiency and shouldn't affect your site's speed.

Q: Is a Gravatar account required?
The plugin doesn’t require API keys or for you to have a Gravatar account.

Q: Does Gravatar track users?
A: IP addresses and site visits are not part of Gravatar profiles. To enhance privacy, we retain only 10% of web and API logs, which are then deleted after around 30 days. More on our privacy pledge and practices can be found <a href="https://support.gravatar.com/account/data-privacy/">here</a>.

Q: Does the Gravatar Enhanced plugin work with WordPress multisite?
A: Yes, the plugin is fully compatible with multisite networks.

Q: How will this plugin affect existing avatars?
A: Existing avatars will gain alt-text and privacy features without visual changes.

Q: Will the Profile Block work with my theme?
A: The block is designed to work with all WordPress themes that support the block editor.

Q: How does the commenter email feature work?
A: It sends a single, polite email to commenters without Gravatars, inviting them to create one. You can customize the content of the email or disable this feature, too.

== Automatic Installation ==

1. Click 'Add New Plugin' from your WordPress plugins page and search for 'Gravatar Enhanced'
1. Press the 'Install Now' button
1. Activate the plugin
1. Go to the "Discussion" Settings page to enable the new features.

== Manual Installation ==

1. Download the plugin from https://wordpress.org/plugins/gravatar-enhanced/
1. Upload and extract the plugin to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the "Discussion" Settings page to enable the new features.

== Screenshots ==

1. Gravatar card on profile page
2. Avatar options
3. Gravatar block

== Changelog ==

= 0.5.0 =
* Release Gravatar profile block v0.2.0

= 0.4.0 =
* New feature: integration with WooCommerce on My Account page
* New feature: show avatar on WooCommerce customers page

= 0.3.2 =
* Hide verified accounts that are set to hide from Gravatar hovercards

= 0.3.1 =
* Fix hardcoded address on profile page

= 0.3.0 =
* Use SHA256 instead of MD5
* Add a referrerpolicy option
* Add option to force default avatar style
* Add plugin uninstall function
* Add user name to avatar alt tag
* Add local proxy for Gravatar images
* Add avatar and hovercard blocks to Gutenberg
* Add quick access to uploading an image to Gravatar from profile page
* Add Gravatar Profile block
* Updated Hovercards library to v0.9.0
* Hovercards are enabled by default, unless you previously disabled them
* Rearrange profile page to move profile fields to the top and settings to the bottom

See the previous changelogs in changelog.txt.
