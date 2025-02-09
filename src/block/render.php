<?php
$layout_class_map = [
	'portrait' => 'gravatar-block--portrait',
	'landscape' => 'gravatar-block--landscape',
	'line' => 'gravatar-block--line',
];
$layout_class = isset( $layout_class_map[ $attributes['layout'] ] ) ? ' ' . $layout_class_map[ $attributes['layout'] ] : '';
$custom_text_color_class = isset( $attributes['textColor'] ) ? ' gravatar-block--custom-text-color' : '';
$class = 'gravatar-block' . $layout_class . $custom_text_color_class;

$email = strtolower( trim( $attributes['userEmail'] ) );
$sanitized_email = sanitize_email( $email );
$data = wp_json_encode(
	[
		'hashedEmail' => hash( 'sha256', $sanitized_email ),
		'layout' => $attributes['layout'],
		'avatarUrlSizeParam' => $attributes['avatarUrlSizeParam'],
		'placeholderProfile' => $attributes['placeholderProfile'],
		'deletedElements' => $attributes['deletedElements'],
	]
);
$attrs = get_block_wrapper_attributes(
	[
		'class' => $class,
		'data-attrs' => $data,
	]
);
?>
<div <?php echo wp_kses_data( $attrs ); ?>></div>
