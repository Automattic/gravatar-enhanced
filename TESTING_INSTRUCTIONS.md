# Testing Instructions - Issue #29

Synchronize the verified-account icon color with the block's text color.

## Install

1. Checkout the branch and build the plugin:

```bash
git checkout fix/29-sync-verified-accounts-icon-color
yarn install
yarn build
```

2. Copy the plugin folder into `wp-content/plugins/` of a WordPress site (PHP >= 7.4, WordPress >= 6.6):
   - either link `gravatar-enhanced` from this repo into `wp-content/plugins/`, or
   - run `yarn release` and copy the resulting `release/` folder into `wp-content/plugins/gravatar-enhanced/`.

3. Activate "Gravatar Enhanced" from the Plugins screen.

## Test Steps

Requires a profile with verified accounts (social links). Add one via gravatar.com, or use a theme/plugin that supplies an author email with verified accounts.

1. Create/edit a post and insert the **Gravatar Profile** block (Editor > block inserter).
   - Set the user/email to a profile that has verified social accounts.
   - Pick the Default or Portrait layout.

2. **Set a custom text color:**
   - With the block selected, open the block settings sidebar > Color > Text color.
   - Pick a light color (e.g. cyan) and set the block or page background to a dark color.
   - Check the published page:
     - The verified-account icons (including the Gravatar marker) now render as solid silhouettes in the **chosen text color**, matching the text.
     - The avatar photo is unchanged (still a photo, never a silhouette).
     - Link still opens the account's profile in a new tab.

3. **Set a preset text color:**
   - Pick a theme text-color preset (e.g. white) instead of a custom swatch.
   - Check the published page: icons match that preset color too.

4. **Remove the custom color:**
   - Clear the text color selection.
   - Check the published page: icons return to the original brand-color SVGs, exactly as before this change.

5. **Portrait layout:**
   - Switch the block to Portrait and repeat steps 2-4. Verified-accounts group icons behave the same.

6. **No regression on the avatar and text:**
   - Confirm the avatar (image), display name, job title, company, location, description, and "View profile" link look exactly like before in both default and portrait layouts.

7. **Console check:**
   - Open the browser console on the published page. No CORS or script errors should appear.

8. **Cross-check quickly in Firefox** (and Safari if available) for the same behavior.

## Expected Result

With a custom/preset text color set, verified-account icons use that exact color. With no custom color set, behavior is unchanged (original icons, no masking). The avatar is never masked.
