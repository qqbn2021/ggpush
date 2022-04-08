<?php
// 直接访问报404错误
if ( ! function_exists( 'add_action' ) ) {
	http_response_code( 404 );
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Clear push record', 'ggpush' ); ?></h1>
	<p>
		<?php _e( 'Success', 'ggpush' ); ?>
	</p>
	<a class="button button-primary" href="<?php echo esc_url($current_url);?>"><?php _e( 'Go back', 'ggpush' ); ?></a>
</div>