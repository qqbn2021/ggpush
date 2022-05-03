<?php
/**
 * Plugin Name:果果推送
 * Plugin URI:https://dev.ggdoc.cn/plugin/1.html
 * Description:支持百度搜索引擎的普通、快速收录、微软Bing搜索引擎、以及IndexNow方式的Api提交链接功能，同时还支持定时提交链接功能。
 * Version:0.0.2
 * Requires at least: 5.0
 * Requires PHP:5.3
 * Author:果果开发
 * Author URI:https://dev.ggdoc.cn
 * License:GPL v2 or later
 */

// 直接访问报404错误
if (!function_exists('add_action')) {
    http_response_code(404);
    exit;
}
if (defined('GGPUSH_PLUGIN_DIR')) {
    // 在我的插件那添加重名插件说明
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('Ggpush_Plugin', 'duplicate_name_ggpush'));
    return;
}
// 插件目录后面有 /
define('GGPUSH_PLUGIN_DIR', plugin_dir_path(__FILE__));
// 保证时区正确
$timezone_string = get_option('timezone_string');
if (!empty($timezone_string)) {
    date_default_timezone_set($timezone_string);
}
/**
 * 自动加载
 * @param string $class
 * @return void
 */
function ggpush_autoload($class)
{
    $class_file = GGPUSH_PLUGIN_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $class)) . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
}

spl_autoload_register('ggpush_autoload');
// 启用插件
register_activation_hook(__FILE__, array('Ggpush_Plugin', 'plugin_activation'));
// 删除插件
register_uninstall_hook(__FILE__, array('Ggpush_Plugin', 'plugin_uninstall'));
// 禁用插件
register_deactivation_hook(__FILE__, array('Ggpush_Plugin', 'plugin_deactivation'));
// 添加页面
add_action('admin_init', array('Ggpush_Plugin', 'admin_init'));
// 添加菜单
add_action('admin_menu', array('Ggpush_Plugin', 'admin_menu'));
// 在我的插件那添加设置的链接
add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('Ggpush_Plugin', 'link_ggpush'));
// 定时任务执行时间间隔
add_filter('cron_schedules', array('Ggpush_Cron', 'ggpush_cron_schedules'));
add_action('ggpush_run_baidu_cron', array('Ggpush_Cron', 'ggpush_run_baidu_cron'));
add_action('ggpush_run_baidu_fast_cron', array('Ggpush_Cron', 'ggpush_run_baidu_fast_cron'));
add_action('ggpush_run_bing_cron', array('Ggpush_Cron', 'ggpush_run_bing_cron'));
add_action('ggpush_run_indexnow_cron', array('Ggpush_Cron', 'ggpush_run_indexnow_cron'));
// 发布新文章时推送链接
add_action('wp_insert_post', array('Ggpush_Plugin', 'ggpush_to_publish'), 99, 3);