<?php
// 直接访问报404错误
if ( ! function_exists( 'add_action' ) ) {
	http_response_code( 404 );
	exit;
}
