<?php
$sanitized_email = sanitize_email( $attributes['userEmail'] ) ?: '';
$data = wp_json_encode( [
    'hashedEmail' => hash( 'sha256', $sanitized_email ),
    'deletedElements' => $attributes['deletedElements'],
] );
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <div class="gravatar-block" data-attrs="<?php echo esc_attr( $data ); ?>"></div>
</div>
