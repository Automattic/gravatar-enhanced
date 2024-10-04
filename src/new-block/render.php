<?php
$sanitized_email = sanitize_email( $attributes['userEmail'] ) ?: '';
$data = wp_json_encode( [
    'hashedEmail' => hash( 'sha256', $sanitized_email ),
    'deletedElements' => $attributes['deletedElements'],
] );
$class = 'gravatar-block' . ( isset( $attributes['textColor'] ) ? ' gravatar-block--custom-text-color' : '' );
$attrs = get_block_wrapper_attributes( [
    'class' => $class,
    'data-attrs' => $data,
] );
?>
<div <?php echo $attrs; ?>></div>
