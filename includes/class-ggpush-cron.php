<?php

/**
 * 定时任务
 */
class Ggpush_Cron
{
    /**
     * 定时任务过滤器
     *
     * @param $schedules
     *
     * @return mixed
     */
    public static function cron_schedules($schedules)
    {
        // 百度普通收录
        $baidu_cron_interval = Ggpush_Plugin::get_option('baidu_interval', 0);
        if ($baidu_cron_interval > 0) {
            $schedules['ggpush_baidu_cron'] = array(
                'interval' => $baidu_cron_interval * 60,
                'display' => '百度普通收录',
            );
        } else {
            unset($schedules['ggpush_baidu_cron']);
        }

        // 百度快速收录
        $baidu_fast_cron_interval = Ggpush_Plugin::get_option('baidu_fast_interval', 0);
        if ($baidu_fast_cron_interval > 0) {
            $schedules['ggpush_baidu_fast_cron'] = array(
                'interval' => $baidu_fast_cron_interval * 60,
                'display' => '百度快速收录',
            );
        } else {
            unset($schedules['ggpush_baidu_fast_cron']);
        }

        // bing收录
        $bing_cron_interval = Ggpush_Plugin::get_option('bing_interval', 0);
        if ($bing_cron_interval > 0) {
            $schedules['ggpush_bing_cron'] = array(
                'interval' => $bing_cron_interval * 60,
                'display' => '必应收录',
            );
        } else {
            unset($schedules['ggpush_bing_cron']);
        }

        // indexnow收录
        $indexnow_cron_interval = Ggpush_Plugin::get_option('indexnow_interval', 0);
        if ($indexnow_cron_interval > 0) {
            $schedules['ggpush_indexnow_cron'] = array(
                'interval' => $indexnow_cron_interval * 60,
                'display' => 'IndexNow收录',
            );
        } else {
            unset($schedules['ggpush_indexnow_cron']);
        }

        return $schedules;
    }

    /**
     * 百度普通推送
     */
    public static function ggpush_run_baidu_cron()
    {
        $urls = Ggpush_Common::get_post_url(Ggpush_Plugin::get_option('baidu_num'), Ggpush_Plugin::get_option('baidu_type'));
        if (!empty($urls)) {
            Ggpush_Common::push_baidu($urls);
        }
    }

    /**
     * 百度快速推送
     */
    public static function ggpush_run_baidu_fast_cron()
    {
        $urls = Ggpush_Common::get_post_url(Ggpush_Plugin::get_option('baidu_fast_num'), Ggpush_Plugin::get_option('baidu_fast_type'));
        if (!empty($urls)) {
            Ggpush_Common::push_baidu($urls, true);
        }
    }

    /**
     * bing推送
     */
    public static function ggpush_run_bing_cron()
    {
        $urls = Ggpush_Common::get_post_url(Ggpush_Plugin::get_option('bing_num'), Ggpush_Plugin::get_option('bing_type'));
        if (!empty($urls)) {
            Ggpush_Common::push_bing($urls);
        }
    }

    /**
     * indexnow推送
     */
    public static function ggpush_run_indexnow_cron()
    {
        $urls = Ggpush_Common::get_post_url(Ggpush_Plugin::get_option('indexnow_num'), Ggpush_Plugin::get_option('indexnow_type'));
        if (!empty($urls)) {
            Ggpush_Common::push_indexnow($urls);
        }
    }

    /**
     * 创建百度普通收录定时任务
     */
    public static function ggpush_create_baidu_cron()
    {
        if (GGPUSH_RUN_BAIDU_CRON) {
            if (!wp_next_scheduled('ggpush_run_baidu_cron')) {
                wp_schedule_event(time(), 'ggpush_baidu_cron', 'ggpush_run_baidu_cron');
            }
        } else {
            self::ggpush_delete_baidu_cron();
        }
    }

    /**
     * 删除百度普通收录定时任务
     */
    public static function ggpush_delete_baidu_cron()
    {
        $timestamp = wp_next_scheduled('ggpush_run_baidu_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ggpush_run_baidu_cron');
        }
    }

    /**
     * 创建百度快速收录定时任务
     */
    public static function ggpush_create_baidu_fast_cron()
    {
        if (GGPUSH_RUN_BAIDU_FAST_CRON) {
            if (!wp_next_scheduled('ggpush_run_baidu_fast_cron')) {
                wp_schedule_event(time(), 'ggpush_baidu_fast_cron', 'ggpush_run_baidu_fast_cron');
            }
        } else {
            self::ggpush_delete_baidu_fast_cron();
        }
    }

    /**
     * 删除百度快速收录定时任务
     */
    public static function ggpush_delete_baidu_fast_cron()
    {
        $timestamp = wp_next_scheduled('ggpush_run_baidu_fast_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ggpush_run_baidu_fast_cron');
        }
    }

    /**
     * 创建bing收录定时任务
     */
    public static function ggpush_create_bing_cron()
    {
        if (GGPUSH_RUN_BING_CRON) {
            if (!wp_next_scheduled('ggpush_run_bing_cron')) {
                wp_schedule_event(time(), 'ggpush_bing_cron', 'ggpush_run_bing_cron');
            }
        } else {
            self::ggpush_delete_bing_cron();
        }
    }

    /**
     * 删除bing收录定时任务
     */
    public static function ggpush_delete_bing_cron()
    {
        $timestamp = wp_next_scheduled('ggpush_run_bing_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ggpush_run_bing_cron');
        }
    }

    /**
     * 创建indexnow收录定时任务
     */
    public static function ggpush_create_indexnow_cron()
    {
        if (GGPUSH_RUN_INDEXNOW_CRON) {
            $indexnow_token = Ggpush_Plugin::get_option('indexnow_token');
            $keyLocation = ABSPATH . 'ggpush-' . $indexnow_token . '.txt';
            file_put_contents($keyLocation, $indexnow_token);
            if (!wp_next_scheduled('ggpush_run_indexnow_cron')) {
                wp_schedule_event(time(), 'ggpush_indexnow_cron', 'ggpush_run_indexnow_cron');
            }
        } else {
            self::ggpush_delete_indexnow_cron();
        }
    }

    /**
     * 删除indexnow收录定时任务
     */
    public static function ggpush_delete_indexnow_cron()
    {
        $timestamp = wp_next_scheduled('ggpush_run_indexnow_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ggpush_run_indexnow_cron');
        }
        $indexnow_token = Ggpush_Plugin::get_option('indexnow_token');
        if (!empty($indexnow_token)) {
            $keyLocation = ABSPATH . 'ggpush-' . $indexnow_token . '.txt';
            wp_delete_file($keyLocation);
        }
    }
}