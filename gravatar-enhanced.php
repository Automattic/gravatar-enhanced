<?php
/*
Plugin Name: Gravatar Enhanced
Plugin URI: http://wordpress.org/extend/plugins/gravatar-enhanced/
Description: Enhanced functionality for Gravatar-ifying your WordPress site. Once you've enabled the plugin, go to the "Avatars" section on the <a href="options-discussion.php">Discussion Settings page</a> to get started.
Author: Mohammad Jangda, Automattic Inc.
Version: 0.1.1
License: GPL2 (see below)

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

define( 'GRAVATAR_ENHANCED_VERSION', '0.1.1' );
define( 'GRAVATAR_ENHANCED_SIGNUP_URL', 'http://www.gravatar.com/signup' );
define( 'GRAVATAR_ENHANCED_HOVERCARD_URL', 'http://s.gravatar.com/js/gprofiles.js' );
define( 'GRAVATAR_ENHANCED_HOVERCARD_VERSION', 'e' );

/**
 * Adds retro Gravatar type to defaults
 * 
 * @since 0.1
 */
function gravatar_enhanced_add_new_defaults( $defaults ) {
	if( ! isset( $defaults['retro'] ) )
		$defaults['retro'] = __( 'Retro (Generated)', 'gravatar-enhanced' );
	
	return $defaults;
}
add_filter( 'avatar_defaults', 'gravatar_enhanced_add_new_defaults' );

/**
 * Add Gravatar Hovercards (grofiles) script if enabled
 * Credit: Otto (http://ottopress.com/2010/gravatar-hovercards/)
 * 
 * @since 0.1
 */
function gravatar_enhanced_add_hovercards() {
	if( get_option( 'gravatar_hovercards' ) ) {
		wp_enqueue_script( 'gprofiles', GRAVATAR_ENHANCED_HOVERCARD_URL, array( 'jquery' ), GRAVATAR_ENHANCED_HOVERCARD_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts','gravatar_enhanced_add_hovercards' );

/**
 * Callback to handle output for gravatar checkbox settings
 * 
 * @since 0.1
 */
function gravatar_enhanced_checkbox_setting( $args ) {
	$value = get_option( $args['id'] );
	?>
	<p>
		<label for="<?php echo $args['id'] ?>">
			<input name="<?php echo $args['id'] ?>" id="<?php echo $args['id'] ?>" type="checkbox" value="1" <?php echo checked( 1, $value, false ); ?> />
			<?php echo $args['label']; ?>
			<?php if( $args['description'] ) : ?>
				<br />
				<span class="description"><?php echo $args['description']; ?></span>
			<?php endif; ?>
		</label>
	</p>
	<?php
	do_action( "post_{$args['id']}", $args );
}

/**
 * Adds a textbox to allow users to configure the invitation message
 * 
 * @since 0.1
 */
function gravatar_enhanced_invitation_message_setting() {
	$value = get_option( 'gravatar_invitation_message' );
	?>
	<p>
		<label for="gravatar_invitation_message"><?php _e( 'Customize the invitation message:', 'gravatar-enhanced' ); ?></label>
	</p>
	<p>
		<textarea id="gravatar_invitation_message" name="gravatar_invitation_message" rows="10" cols="50" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
		<br />
		<label for="gravatar_invitation_message"><span class="description"><?php _e( 'Why not send your commenters a personalized message? You can use placeholders like COMMENTER_NAME, COMMENTER_EMAIL, COMMENTER_URL, SITE_URL, and POST_NAME. Make sure to include GRAVATAR_URL somewhere in the message!', 'gravatar-enhanced' ); ?></span></label>
	</p>
	<?php
}

/**
 * Handle when new comments are created.
 * We have to hook into wp_insert_comment too because it doesn't call transition_comment_status :( 
 *
 * @since 0.1
 * @param mixed $id
 * @param mixed $comment
 */
function gravatar_enhanced_insert_comment( $id, $comment ) {
	
	$comment_status = $comment->comment_approved;
	
	// We only send emails for approved comments
	if( empty( $comment_status ) || ! in_array( $comment_status, array( 1, '1', 'approved' ) ) )
		return;
	
	gravatar_enhanced_notify_commenter( $comment->comment_author_email, $comment );
}
add_action( 'wp_insert_comment', 'gravatar_enhanced_insert_comment', 10, 2 );

/**
 * Handle when new comments are updated or approved.
 * 
 * @since 0.1
 * @param mixed $new_status
 * @param mixed $old_status
 * @param mixed $comment
 */
function gravatar_enhanced_transition_comment( $new_status, $old_status, $comment ) {

	// We only send emails for approved comments
	if( 'approved' != $new_status || 'approved' == $old_status )
		return;
	
	// Only send emails for comments less than a week old
	if( get_comment_date( 'U', $comment->comment_ID ) < strtotime( apply_filters( 'gravatar_enhanced_invitation_time_limit', '-1 week' ) ) )
		return;

	gravatar_enhanced_notify_commenter( $comment->comment_author_email, $comment );
}
add_action( 'transition_comment_status', 'gravatar_enhanced_transition_comment', 10, 3 );

/**
 * Send gravatar invitation to commenters if enabled, if they don't have a gravatar and we haven't notified them already.
 * 
 * @since 0.1
 * @param mixed $email
 * @param mixed $comment
 */
function gravatar_enhanced_notify_commenter( $email, $comment ) {
	
	// Check that it's a comment and that we have an email address
	if( ! in_array( $comment->comment_type, array( '', 'comment' ) ) || ! $email )
		return;
	
	$post = get_post( $comment->comment_post_ID );

	// Check that the post type supports gravatar invitations
	if( ! post_type_supports( $post->post_type, 'gravatar_invitation_email' ) )
		return;

	if( ! gravatar_enhanced_email_has_gravatar( $email ) && ! gravatar_enhanced_have_notified_commenter( $email ) ) {

		if ( is_multisite() )
			$sitename = get_current_site()->site_name;
		else
			$sitename = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$subject = sprintf( __('[%s] Gravatar Invitation'), $sitename );
		$subject = apply_filters( 'gravatar_enhanced_invitation_subject', $subject, $comment );
		
		$message = stripslashes( get_option( 'gravatar_invitation_message' ) );
	
		if ( $message == false )
			$message = gravatar_enhanced_get_default_invitation_message();

		// Just in case we're missing the signup URL
		if( strpos( $message, 'GRAVATAR_URL' ) === false )
			$message .= "\n\n" . __( 'Sign up now: ', 'gravatar-enhanced' ) . 'GRAVATAR_URL';

		// TODO: Need a better way to handle these for i18n since this does not translate well.
		$message = str_replace( 'SITE_NAME', $sitename, $message );
		$message = str_replace( 'POST_NAME', $post->post_title, $message );
		$message = str_replace( 'COMMENTER_NAME', $comment->comment_author, $message );
		$message = str_replace( 'COMMENTER_EMAIL', $email, $message );
		$message = str_replace( 'COMMENTER_URL', $comment->comment_author_url, $message );
		$message = str_replace( 'GRAVATAR_URL', GRAVATAR_ENHANCED_SIGNUP_URL, $message );

		// Grab author of the post
		$post_author = get_userdata( $post->post_author );

		// Set From header to SITE_NAME
		$wp_email = 'wordpress@' . preg_replace( '#^www\.#', '', strtolower( $_SERVER['SERVER_NAME'] ) );

		// If the post author has a valid email, set the reply to the email 'from' them.
		$reply_name = ! empty( $post_author->user_email ) ? $post_author->display_name : $sitename;
		$reply_email = ! empty( $post_author->user_email ) ? $post_author->user_email : get_option( 'admin_email' );

		$message_headers = array(
			'from' => sprintf( 'From: "%1$s" <%2$s>', $sitename, $wp_email ),
			'type' => sprintf( 'Content-Type: %1$s; charset="%2$s"', 'text/plain', get_option( 'blog_charset' ) ),
			'replyto' => sprintf( 'Reply-To: %1$s <%2$s>', $reply_name, $reply_email ),
		);

		// Pass through filters
		$message = apply_filters( 'gravatar_enhanced_invitation_message', $message, $comment );
		$message_headers = apply_filters( 'gravatar_enhanced_invitation_message_headers', $message_headers, $comment );
		$message_headers = implode( "\n", $message_headers );

		wp_mail( $email, $subject, $message, $message_headers );

		gravatar_enhanced_set_notified_commenter( $email, $comment );
	}
}

/**
 * Mark the commenter as notified.
 * 
 * @since 0.1
 * @param mixed $email
 */
function gravatar_enhanced_set_notified_commenter( $email, $comment ) {
	update_metadata( 'comment', $comment->comment_ID, gravatar_enhanced_get_notify_key( $email ), 1 );
}

/**
 * Check to see if we've notified the commenter already.
 * 
 * @since 0.1
 * @param mixed $email
 * @return bool
 */
function gravatar_enhanced_have_notified_commenter( $email ) {
	global $wpdb;
	$table = _get_meta_table( 'comment' );
	return $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$table} WHERE meta_key = %s LIMIT 1", gravatar_enhanced_get_notify_key( $email ) ) );
}

/**
 * Build the key we use to store comment notifications.
 * 
 * @since 0.1
 * @param mixed $email
 * @return string
 */
function gravatar_enhanced_get_notify_key( $email ) {
	return sprintf( 'gravatar_invite_%s', md5( strtolower( $email ) ) );
}

/**
 * The default invitation message
 * 
 * @since 0.1
 * @return string
 */
function gravatar_enhanced_get_default_invitation_message() {
	return stripslashes( __( 'Hi COMMENTER_NAME!

Thanks for your comment on "POST_NAME"!

I noticed that you didn\'t have your own picture or profile next to your comment. Why not set one up using Gravatar? Click the link below to get started:

GRAVATAR_URL

*What\'s a Gravatar?* 
Your Gravatar (a Globally Recognized Avatar) is an image that follows you from site to site appearing beside your name when you do things like comment or post on a blog. Avatars help identify your posts on blogs and web forums, so why not on any site?

Thanks for visiting and come back soon!

-- The Team @ SITE_NAME', 'gravatar-enhanced' ) );

}

/**
 * Checks to see if a given email has an associated gravatar.
 * 
 * @since 0.1
 * @param mixed $email
 * @return bool
 */
function gravatar_enhanced_email_has_gravatar( $email ) {
	if ( empty( $email ) )
		return false;
	
	$email_hash = md5( strtolower( $email ) );

	if ( is_ssl() )
		$host = 'https://secure.gravatar.com';
	else
		$host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash[0] ) % 2 ) );

	$url = sprintf( '%s/avatar/%s?d=404', $host, $email_hash );
	$request = new WP_Http();
	$result = $request->request( $url, array( 'method' => 'GET' ) );
	
	// If gravatar returns a 404, email doesn't have a gravatar attached
	if( is_array( $result ) && isset( $result['response']['code'] ) && $result['response']['code'] == 404 )
		return false;
		
	// For all other cases, let's assume we do
	return true;
}


/**
 * Main admin_init function used to hook into and register stuff and init plugin settings.
 * 
 * @since 0.1
 */
function gravatar_enhanced_admin_init() {
	register_setting( 'discussion', 'gravatar_hovercards' );
	add_settings_field( 'gravatar_hovercards', __( 'Hovercards', 'gravatar-enhanced' ), 'gravatar_enhanced_checkbox_setting', 'discussion', 'avatars', array(
		'id' => 'gravatar_hovercards',
		'label' => __( 'Enable Gravatar Hovercards', 'gravatar-enhanced' ),
		'description' => __( 'Gravatar Hovercards show information about a person: name, bio, pictures, and their contact info at other services they use on the web like Twitter, Facebook or LinkedIn. <a href="http://blog.gravatar.com/2010/10/06/gravatar-hovercards-on-wordpress-com/" title="Opens new window" target="_blank">Learn More &raquo;</a>', 'gravatar-enhanced' )
	) );
	
	register_setting( 'discussion', 'gravatar_invitation_email' );
	add_settings_field( 'gravatar_invitation_email', __( 'Invitation', 'gravatar-enhanced' ), 'gravatar_enhanced_checkbox_setting', 'discussion', 'avatars', array(
		'id' => 'gravatar_invitation_email',
		'label' => __( 'Send Gravatar Invitations', 'gravatar-enhanced' ),
		'description' => __( 'Send a nice email to commenters without a Gravatar, inviting them to sign up for one!', 'gravatar-enhanced' )
	) );
	
	register_setting( 'discussion', 'gravatar_invitation_message' );
	add_action( 'post_gravatar_invitation_email', 'gravatar_enhanced_invitation_message_setting' );
	
	if( ! get_option( 'gravatar_invitation_message' ) )
		update_option( 'gravatar_invitation_message', gravatar_enhanced_get_default_invitation_message() );
	
}
add_action( 'admin_init', 'gravatar_enhanced_admin_init' );

/**
 * Main init function used to hook into and register stuff.
 * 
 * @since 0.1
 */
function gravatar_enhanced_init() {
	if( get_option( 'gravatar_invitation_email' ) ) {
		foreach( array( 'post', 'page' ) as $post_type )
			add_post_type_support( $post_type, 'gravatar_invitation_email' );
	}
	
	load_plugin_textdomain( 'gravatar-enhanced', null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'gravatar_enhanced_init' );
