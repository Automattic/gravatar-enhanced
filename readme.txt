=== Gravatar Enhanced - Avatars, Profiles, and Privacy ===
Contributors: automattic, batmoo, johnny5, aaronfc, wellyshen
Tags: avatar, profile, privacy, comments, profile picture
Tested up to: 6.8
Stable tag: 0.13.0
License: GPLv2

The official Gravatar plugin, featuring privacy-focused settings, easy profile updates, and customizable Gravatar Profile blocks.

== Description ==

Elevate your WordPress site with Gravatar Enhanced - the plugin that simplifies digital identity and improves user engagement.

Seven Ways Gravatar Enhanced Improves WordPress:

✓ Privacy Protection - Automatic referrer blocking and optional IP address proxy
✓ Accessibility Improvements - Alt-text for all avatars to support screen readers
✓ One-Click Profile Updates - Edit Gravatar directly from the WordPress dashboard
✓ Versatile Profile Block & Patterns - Showcase authors and team members anywhere
✓ Comment Engagement Tools - Remind users to create avatars for better discussions
✓ Comment Form Integration - Show Gravatar directly in the comment form
✓ WooCommerce Integration - Personalized account pages for customers

=== Privacy Protection ===
Just by enabling the plugin, the plugin ensures that no referrer information is sent to Gravatar. The opt-in proxy service also keeps IP addresses from being exposed or logged.

=== Accessibility Improvements ===
All avatars now include alt-text, enhancing the experience for users with screen readers. Alt-text can be edited in the Gravatar Profile editor.

=== One-Click Profile Updates ===
Visit Users > Your Profile to edit your Gravatar profile directly from WordPress. Preview your hovercard and make updates with ease.

=== Gravatar Profile Block & Patterns ===
Enhance your website with our custom Gravatar profile block and patterns. Seamlessly integrate Gravatar profiles into posts, pages, or biographies to elegantly showcase team members, guest contributors, speakers, event attendees, authors, and more.

=== Comment Engagement Tools ===
Automatically remind commenters without avatars to create a Gravatar, increasing visual engagement on your blog.

=== Comment Form Integration ===
When the plugin is enabled we will show a Gravatar profile directly in the comment form, and allow the profile to be updated, so users can feel confident their details are correct, and the site shows richer comments.

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
4. Gravatar pattern

== Changelog ==

= 0.13.0 =
* Further update the quick editor integration on the profile page
* Improve sanitisation of block data
* Use latest hovercards

= 0.12.0 =
* Update the quick editor integration on the profile page
* Fix: Add support for core's SHA-256 handling
* Fix Gravatar card on author archive page when no posts
* Upgrade the `@gravatar-com/hovercards` package to v0.13.0

= 0.11.0 =
* Add a Gravatar invitation link to the Profile block
* Fix support for Author in templates for Profile block.
* Upgrade the `@gravatar-com/hovercards` package to v0.12.0
* Upgrade the `@gravatar-com/quick-editor` package to v0.8.0
* Upgrade the tested up version to 6.8

= 0.10.0 =
* Add Gravatar Quick Editor to comment form
* Remove SCSS files from the release folder
* Fix the profile block's style broken in some themes

= 0.9.0 =
* Add more Gravatar patterns
* Pass comment initials when using initials avatar
* Fix empty profile blocks are rendered in the frontend view
* Replace the Gravatar profile's column block with a group block
* Improve compatibility with quick editor and other plugins
* Fix quick editor language support for Taiwanese

= 0.8.0 =
* Add the first Gravatar block pattern
* Add color and initials default avatar
* Upgrade the `@gravatar-com/hovercards` package to v0.10.4
* Fix multiple instances of quick editor

= 0.7.0 =
* Update @gravatar-com/hovercards package version to v0.10.3

= 0.6.0 =
* Add embedded Gravatar profile cards

= 0.5.1 =
* Fix broken Gravatar Profile block

= 0.5.0 =
* Make Gravatar profile block customizable (v0.2.0)

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
