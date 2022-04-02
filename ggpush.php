<?php
/**
 * Plugin Name:Ggpush
 * Plugin URI:https://github.com/qqbn2021/ggpush
 * Description:推送文章链接至搜索引擎，实现链接收录。
 * Version:0.0.1
 * Requires at least: 5.0
 * Requires PHP:7.0
 * Author:Ggdoc
 * Author URI:https://www.ggdoc.cn
 * License:GPL v2 or later
 * Text Domain:ggpush
 * Domain Path:/languages
 */

// 直接访问报404错误
if ( ! function_exists( 'add_action' ) ) {
	http_response_code( 404 );
	exit;
}
// 定义本插件的版本号
const GGPUSH_VERSION = '0.0.1';
define( 'GGPUSH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once GGPUSH_PLUGIN_DIR . 'class-ggpush.php';
require_once GGPUSH_PLUGIN_DIR . 'common-ggpush.php';
load_plugin_textdomain( 'ggpush' );

// 启用插件
register_activation_hook( __FILE__, 'ggpush_plugin_activation' );
// 删除插件
register_uninstall_hook( __FILE__, 'ggpush_plugin_uninstall' );
// 禁用插件
register_deactivation_hook( __FILE__, 'ggpush_plugin_deactivation' );
// 添加页面
add_action( 'admin_init', 'ggpush_page_init' );
// 添加菜单
add_action( 'admin_menu', 'ggpush_options_page' );
// 在我的插件那添加设置的链接
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ggpush_add_settings_link' );
// 定时任务执行时间间隔
add_filter( 'cron_schedules', 'ggpush_cron_schedules' );
add_action( 'ggpush_run_baidu_cron', 'ggpush_run_baidu_cron' );
add_action( 'ggpush_run_baidu_fast_cron', 'ggpush_run_baidu_fast_cron' );
add_action( 'ggpush_run_bing_cron', 'ggpush_run_bing_cron' );
add_action( 'ggpush_run_indexnow_cron', 'ggpush_run_indexnow_cron' );

