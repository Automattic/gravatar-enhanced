<?php

/**
 * @var array{
 *   userEmail: string,
 *   textColor?: string,
 *   userType: string,
 *   layout: string,
 *   avatarUrlSizeParam: string,
 *   placeholderProfile: array{
 *     display_name: string,
 *     job_title: string,
 *     company: string,
 *     location: string,
 *     description: string
 *   },
 *   deletedElements: array<string, bool>
 * } $attributes
 */

$email = strtolower( trim( $attributes['userEmail'] ) );
$email = sanitize_email( $email );

// If the `userType` is email, but the email isn't valid, don't render the block.
if ( $attributes['userType'] === 'email' && ! $email ) {
	return;
}

$layout_class_map = [
	'portrait' => 'gravatar-block--portrait',
	'landscape' => 'gravatar-block--landscape',
	'line' => 'gravatar-block--line',
];
$layout_class = isset( $layout_class_map[ $attributes['layout'] ] ) ? ' ' . $layout_class_map[ $attributes['layout'] ] : '';
$custom_text_color_class = isset( $attributes['textColor'] ) ? ' gravatar-block--custom-text-color' : '';
$class = 'gravatar-block' . $layout_class . $custom_text_color_class;

$data = wp_json_encode(
	[
		'userType' => $attributes['userType'],
		'hashedEmail' => hash( 'sha256', $email ),
		'layout' => $attributes['layout'],
		'avatarUrlSizeParam' => $attributes['avatarUrlSizeParam'],
		'placeholderProfile' => $attributes['placeholderProfile'],
		'deletedElements' => $attributes['deletedElements'],
	]
);

if ( ! $data ) {
	return;
}

$attrs = get_block_wrapper_attributes(
	[
		'class' => $class,
		'data-attrs' => $data,
	]
);
?>
<div <?php echo wp_kses_data( $attrs ); ?>></div>
