<?php
/**
 * Plugin Name:果果推送
 * Plugin URI:https://www.ggdoc.cn/plugin/1.html
 * Description:支持百度搜索引擎的普通、快速收录、微软Bing搜索引擎、以及IndexNow方式的Api提交链接功能，同时还支持定时提交链接功能。
 * Version:0.0.4
 * Requires at least: 5.0
 * Requires PHP:5.3
 * Author:果果开发
 * Author URI:https://www.ggdoc.cn
 * License:GPL v2 or later
 */

// 直接访问报404错误
if (!function_exists('add_action')) {
    http_response_code(404);
    exit;
}
// 插件目录后面有 /
const GGPUSH_PLUGIN_FILE = __FILE__;
define('GGPUSH_PLUGIN_DIR', plugin_dir_path(GGPUSH_PLUGIN_FILE));
// 定义配置
$ggpush_options = get_option('ggpush_options', array());
// 定义上次错误信息
$ggpush_error = '';
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
register_activation_hook(GGPUSH_PLUGIN_FILE, array('Ggpush_Plugin', 'plugin_activation'));
// 删除插件
register_uninstall_hook(GGPUSH_PLUGIN_FILE, array('Ggpush_Plugin', 'plugin_uninstall'));
// 禁用插件
register_deactivation_hook(GGPUSH_PLUGIN_FILE, array('Ggpush_Plugin', 'plugin_deactivation'));
// 添加页面
add_action('admin_init', array('Ggpush_Plugin', 'admin_init'));
// 添加菜单
add_action('admin_menu', array('Ggpush_Plugin', 'admin_menu'));
// 在我的插件那添加设置的链接
add_filter('plugin_action_links_' . plugin_basename(GGPUSH_PLUGIN_FILE), array('Ggpush_Plugin', 'setups'));
// 定时任务执行时间间隔
add_filter('cron_schedules', array('Ggpush_Cron', 'cron_schedules'));
// 百度普通收录
if (!empty($ggpush_options['baidu_token']) && !empty($ggpush_options['baidu_interval']) && !empty($ggpush_options['baidu_num'])) {
    define('GGPUSH_RUN_BAIDU_CRON', true);
    add_action('ggpush_run_baidu_cron', array('Ggpush_Cron', 'ggpush_run_baidu_cron'));
} else {
    define('GGPUSH_RUN_BAIDU_CRON', false);
}
// 百度快速收录
if (!empty($ggpush_options['baidu_token']) && !empty($ggpush_options['baidu_fast_interval']) && !empty($ggpush_options['baidu_fast_num'])) {
    define('GGPUSH_RUN_BAIDU_FAST_CRON', true);
    add_action('ggpush_run_baidu_fast_cron', array('Ggpush_Cron', 'ggpush_run_baidu_fast_cron'));
} else {
    define('GGPUSH_RUN_BAIDU_FAST_CRON', false);
}
// 必应推送
if (!empty($ggpush_options['bing_token']) && !empty($ggpush_options['bing_interval']) && !empty($ggpush_options['bing_num'])) {
    define('GGPUSH_RUN_BING_CRON', true);
    add_action('ggpush_run_bing_cron', array('Ggpush_Cron', 'ggpush_run_bing_cron'));
} else {
    define('GGPUSH_RUN_BING_CRON', false);
}
// indexnow推送
if (!empty($ggpush_options['indexnow_token']) && !empty($ggpush_options['indexnow_interval']) && !empty($ggpush_options['indexnow_num']) && !empty($ggpush_options['indexnow_search_engine'])) {
    define('GGPUSH_RUN_INDEXNOW_CRON', true);
    add_action('ggpush_run_indexnow_cron', array('Ggpush_Cron', 'ggpush_run_indexnow_cron'));
} else {
    define('GGPUSH_RUN_INDEXNOW_CRON', false);
}
// 发布新文章时推送链接
if (!empty($ggpush_options['publish_article_platform'])) {
    // 添加js文件：发布文章后推送
    add_action('admin_enqueue_scripts', array('Ggpush_Plugin', 'admin_enqueue_scripts'));
    // 添加发布文章后的处理动作
    add_action('wp_ajax_ggpush_publish', array('Ggpush_Plugin', 'wp_ajax_ggpush_publish'));
}