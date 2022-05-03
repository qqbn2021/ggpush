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
    public static function ggpush_cron_schedules($schedules)
    {
        $options = get_option('ggpush_options');

        // 百度普通收录
        $baidu_cron_interval = intval(isset($options['ggpush_baidu_interval']) ? $options['ggpush_baidu_interval'] : 0);
        if ($baidu_cron_interval > 0) {
            $schedules['ggpush_baidu_cron'] = array(
                'interval' => $baidu_cron_interval * 60,
                'display' => '百度普通收录',
            );
        } else {
            unset($schedules['ggpush_baidu_cron']);
        }

        // 百度快速收录
        $baidu_fast_cron_interval = intval(isset($options['ggpush_baidu_fast_interval']) ? $options['ggpush_baidu_fast_interval'] : 0);
        if ($baidu_fast_cron_interval > 0) {
            $schedules['ggpush_baidu_fast_cron'] = array(
                'interval' => $baidu_fast_cron_interval * 60,
                'display' => '百度快速收录',
            );
        } else {
            unset($schedules['ggpush_baidu_fast_cron']);
        }

        // bing收录
        $bing_cron_interval = intval(isset($options['ggpush_bing_interval']) ? $options['ggpush_bing_interval'] : 0);
        if ($bing_cron_interval > 0) {
            $schedules['ggpush_bing_cron'] = array(
                'interval' => $bing_cron_interval * 60,
                'display' => '必应收录',
            );
        } else {
            unset($schedules['ggpush_bing_cron']);
        }

        // indexnow收录
        $indexnow_cron_interval = intval(isset($options['ggpush_indexnow_interval']) ? $options['ggpush_indexnow_interval'] : 0);
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
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_baidu_token']) && !empty($options['ggpush_baidu_interval']) && !empty($options['ggpush_baidu_num'])) {
            $urls = Ggpush_Common::ggpush_get_post_url($options['ggpush_baidu_num'], $options['ggpush_baidu_type'] == 2);
            if (!empty($urls)) {
                Ggpush_Common::ggpush_push_baidu($urls);
            }
        }
    }

    /**
     * 百度快速推送
     */
    public static function ggpush_run_baidu_fast_cron()
    {
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_baidu_token']) && !empty($options['ggpush_baidu_fast_interval']) && !empty($options['ggpush_baidu_fast_num'])) {
            $urls = Ggpush_Common::ggpush_get_post_url($options['ggpush_baidu_fast_num'], $options['ggpush_baidu_fast_type'] == 2);
            if (!empty($urls)) {
                Ggpush_Common::ggpush_push_baidu($urls, true);
            }
        }
    }

    /**
     * bing推送
     */
    public static function ggpush_run_bing_cron()
    {
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_bing_token']) && !empty($options['ggpush_bing_interval']) && !empty($options['ggpush_bing_num'])) {
            $urls = Ggpush_Common::ggpush_get_post_url($options['ggpush_bing_num'], $options['ggpush_bing_type'] == 2);
            if (!empty($urls)) {
                Ggpush_Common::ggpush_push_bing($urls);
            }
        }
    }

    /**
     * indexnow推送
     */
    public static function ggpush_run_indexnow_cron()
    {
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_indexnow_token']) && !empty($options['ggpush_indexnow_interval']) && !empty($options['ggpush_indexnow_num'])) {
            $urls = Ggpush_Common::ggpush_get_post_url($options['ggpush_indexnow_num'], $options['ggpush_indexnow_type'] == 2);
            if (!empty($urls) && !empty($options['ggpush_indexnow_search_engine'])) {
                Ggpush_Common::ggpush_push_indexnow($urls);
            }
        }
    }

    /**
     * 创建百度普通收录定时任务
     */
    public static function ggpush_create_baidu_cron()
    {
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_baidu_token']) && !empty($options['ggpush_baidu_interval']) && !empty($options['ggpush_baidu_num'])) {
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
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_baidu_token']) && !empty($options['ggpush_baidu_fast_interval']) && !empty($options['ggpush_baidu_fast_num'])) {
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
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_bing_token']) && !empty($options['ggpush_bing_interval']) && !empty($options['ggpush_bing_num'])) {
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
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_indexnow_token']) && !empty($options['ggpush_indexnow_interval']) && !empty($options['ggpush_indexnow_num'])) {
            $keyLocation = ABSPATH . 'ggpush-' . $options['ggpush_indexnow_token'] . '.txt';
            file_put_contents($keyLocation, $options['ggpush_indexnow_token']);
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
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_indexnow_token'])) {
            $keyLocation = ABSPATH . 'ggpush-' . $options['ggpush_indexnow_token'] . '.txt';
            wp_delete_file($keyLocation);
        }
    }
}