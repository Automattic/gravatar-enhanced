<?php

$layout_class_map = [
	'portrait' => 'gravatar-block--portrait',
	'landscape' => 'gravatar-block--landscape',
	'line' => 'gravatar-block--line',
];

/**
 * @var array{
 *   userEmail: string,
 *   textColor?: string,
 *   userType: string,
 *   layout: string,
 *   avatarUrlSizeParam: string,
 *   placeholderProfile: string,
 *   deletedElements: array<string>
 * } $attributes
 */
$layout_class = isset( $layout_class_map[ $attributes['layout'] ] ) ? ' ' . $layout_class_map[ $attributes['layout'] ] : '';
$custom_text_color_class = isset( $attributes['textColor'] ) ? ' gravatar-block--custom-text-color' : '';
$class = 'gravatar-block' . $layout_class . $custom_text_color_class;

$email = strtolower( trim( $attributes['userEmail'] ) );
$hashed_email = $email ? hash( 'sha256', sanitize_email( $email ) ) : '';
$data = wp_json_encode(
	[
		'userType' => $attributes['userType'],
		'hashedEmail' => $hashed_email,
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
