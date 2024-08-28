<?php
/**
 * @var array<string, string> $attributes
 */
$block_type = $attributes['type'];
$size = $attributes['size'] ?? 96;
$user_type = $attributes['userType'] ?? 'author';
$user_value = $attributes['userValue'] ?? '';

$email = '';
switch ( $user_type ) {
	default:
	case 'author':
		// Get email from current post (or page) author
		$current_post = get_post();
		if ( $current_post ) {
			$user = get_userdata( intval( $current_post->post_author ) );
			if ( $user ) {
				$email = $user->user_email;
			}
		}
		break;
	case 'user':
		// Get email from user ID
		$user = get_userdata( intval( $user_value ) );
		if ( $user ) {
			$email = $user->user_email;
		}
		break;
	case 'email':
		// Get email from email value
		$email = $user_value;
		break;
}

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php if ( $block_type === 'hovercard' ) { ?>
		<div class="gravatar-hovercard-container" data-hash="<?php echo hash( 'sha256', esc_html( $email ) ); ?>"></div>
	<?php } else { ?>
		<?php echo get_avatar( $email, intval( $size ) ); ?>
	<?php } ?>
</div>
