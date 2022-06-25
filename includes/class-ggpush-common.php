<?php

/**
 * 公共类
 */
class Ggpush_Common
{
    /**
     * 更新定时任务
     * @param bool $enable 是否启用定时任务
     * @return void
     */
    public static function update_cron($enable = true)
    {
        if ($enable) {
            // 插件已激活，启用定时任务
            Ggpush_Cron::ggpush_create_baidu_cron();
            Ggpush_Cron::ggpush_create_baidu_fast_cron();
            Ggpush_Cron::ggpush_create_bing_cron();
            Ggpush_Cron::ggpush_create_indexnow_cron();
        } else {
            // 插件已禁用，删除定时任务
            Ggpush_Cron::ggpush_delete_baidu_cron();
            Ggpush_Cron::ggpush_delete_baidu_fast_cron();
            Ggpush_Cron::ggpush_delete_bing_cron();
            Ggpush_Cron::ggpush_delete_indexnow_cron();
        }
    }

    /**
     * 获取推送文章网址
     *
     * @param int $num 推送数据
     * @param int $type 1 最新 2 随机 3 伪随机
     *
     * @return array
     */
    public static function get_post_url($num, $type)
    {
        $data = array();
        if ($num > 0) {
            $offset = 0;
            $order = 'DESC';
            $orderby = 'ID';
            if (2 == $type) {
                $orderby = 'rand';
            } else if (3 == $type) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'posts';
                $total = $wpdb->get_var('SELECT COUNT(*) FROM `' . $table_name . '`');
                $order = 'ASC';
                if ($total > $num) {
                    $offset = mt_rand(0, $total - $num);
                }
            }
            $args = array(
                'orderby' => $orderby,
                'offset' => $offset,
                'order' => $order,
                'post_status' => 'publish',
                'posts_per_page' => $num
            );
            $the_query = new WP_Query($args);
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $data[] = get_permalink();
            }
        }

        return $data;
    }

    /**
     * 百度推送
     *
     * @param array $urls
     * @param bool $daily
     */
    public static function push_baidu($urls, $daily = false)
    {
        $response = Ggpush::push($urls, parse_url(get_home_url(), PHP_URL_HOST), Ggpush_Plugin::get_option('baidu_token'), $daily ? 'daily' : '');
        if (is_wp_error($response)) {
            $record_result_error = $response->get_error_message();
            $record_result_status = 2;
            $record_result = '';
        } else {
            $record_result = wp_remote_retrieve_body($response);
            $record_result_error = '';
            if (!empty($record_result)) {
                $tmp = json_decode($record_result, true);
                if (!empty($tmp['success']) && $tmp['success'] > 0) {
                    $record_result_status = 1;
                } else {
                    $record_result_status = 2;
                    if (!empty($tmp['message'])) {
                        $record_result_error = $tmp['message'];
                    } else {
                        if (!empty($tmp['not_same_site'])) {
                            $record_result_error = '推送网址错误';
                        }
                    }
                }
            } else {
                $record_result_status = 2;
            }
        }
        $data = array(
            'record_platform' => '1',
            'record_mode' => $daily ? '2' : '1',
            'record_urls' => json_encode($urls),
            'record_num' => count($urls),
            'record_result' => $record_result,
            'record_result_code' => (int)wp_remote_retrieve_response_code($response),
            'record_result_status' => $record_result_status,
            'record_result_error' => $record_result_error,
        );
        self::save_record($data);
    }

    /**
     * 推送链接到bing
     *
     * @param $urls
     */
    public static function push_bing($urls)
    {
        $response = Ggpush::bing_push($urls, get_home_url(), Ggpush_Plugin::get_option('bing_token'));
        if (is_wp_error($response)) {
            $record_result_error = $response->get_error_message();
            $record_result_status = 2;
            $record_result = '';
        } else {
            $record_result = wp_remote_retrieve_body($response);
            $record_result_error = '';
            if (!empty($record_result)) {
                $tmp = json_decode($record_result, true);
                if (is_array($tmp) && array_key_exists('d', $tmp) && $tmp['d'] === null) {
                    $record_result_status = 1;
                } else {
                    $record_result_status = 2;
                    if (!empty($tmp['Message'])) {
                        $record_result_error = $tmp['Message'];
                    }
                }
            } else {
                $record_result_status = 2;
            }
        }
        $data = array(
            'record_platform' => '6',
            'record_mode' => '4',
            'record_urls' => json_encode($urls),
            'record_num' => count($urls),
            'record_result' => $record_result,
            'record_result_code' => (int)wp_remote_retrieve_response_code($response),
            'record_result_status' => $record_result_status,
            'record_result_error' => $record_result_error
        );
        self::save_record($data);
    }

    /**
     * 推送链接到indexnow
     *
     * @param $urls
     */
    public static function push_indexnow($urls)
    {
        $keyLocation = get_home_url() . '/ggpush-' . Ggpush_Plugin::get_option('indexnow_token') . '.txt';
        foreach (Ggpush_Plugin::get_option('indexnow_search_engine', array()) as $v) {
            $host = 'api.indexnow.org';
            $record_platform = 8;
            switch ($v) {
                case 2:
                    $record_platform = 6;
                    $host = 'www.bing.com';
                    break;
                case 3:
                    $record_platform = 9;
                    $host = 'yandex.com';
                    break;
                case 4:
                    $record_platform = 10;
                    $host = 'search.seznam.cz';
                    break;
            }
            $response = Ggpush::index_now_push($urls, parse_url(get_home_url(), PHP_URL_HOST), Ggpush_Plugin::get_option('indexnow_token'), $keyLocation, $host);
            $record_result_code = (int)wp_remote_retrieve_response_code($response);
            if (is_wp_error($response)) {
                $record_result_error = $response->get_error_message();
                $record_result_status = 2;
                $record_result = '';
            } else {
                $record_result = wp_remote_retrieve_body($response);
                $record_result_error = '';
                if (!empty($record_result)) {
                    $record_result = trim(strip_tags($record_result));
                    $record_result_is_arr = json_decode($record_result, true);
                    if (!empty($record_result_is_arr['message'])) {
                        $record_result_error = $record_result_is_arr['message'];
                    }
                }
                if ($record_result_code === 200) {
                    $record_result_status = 1;
                } else {
                    $record_result_status = 2;
                }
            }
            $data = array(
                'record_platform' => $record_platform,
                'record_mode' => '5',
                'record_urls' => json_encode($urls),
                'record_num' => count($urls),
                'record_result' => $record_result,
                'record_result_code' => $record_result_code,
                'record_result_status' => $record_result_status,
                'record_result_error' => $record_result_error
            );
            self::save_record($data);
        }
    }

    /**
     * 保存推送日志记录
     *
     * @param $data
     *
     * @return bool|int|mysqli_result|resource|null
     */
    public static function save_record($data)
    {
        if (empty($data)) {
            return false;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';

        return $wpdb->insert($table_name, $data);
    }

    /**
     * 格式化推送平台
     *
     * @param $record_platform
     *
     * @return mixed|string|void
     */
    public static function format_record_platform($record_platform)
    {
        switch ($record_platform) {
            case 1:
                return '百度';
            case 2:
                return '360';
            case 3:
                return '搜狗';
            case 4:
                return '头条';
            case 5:
                return '神马';
            case 6:
                return '必应';
            case 7:
                return '谷歌';
            case 8:
                return 'IndexNow';
            case 9:
                return 'Yandex';
            case 10:
                return 'Seznam.cz';
        }

        return $record_platform;
    }

    /**
     * 格式化推送方式
     *
     * @param $record_mode
     *
     * @return mixed|string|void
     */
    public static function format_record_mode($record_mode)
    {
        switch ($record_mode) {
            case 1:
                return '普通收录';
            case 2:
                return '快速收录';
            case 3:
                return 'Js提交';
            case 4:
                return 'Api提交';
            case 5:
                return 'IndexNow';
        }

        return $record_mode;
    }

    /**
     * 格式化推送结果
     *
     * @param $result_status
     *
     * @return mixed|string|void
     */
    public static function format_result_status($result_status)
    {
        switch ($result_status) {
            case 1:
                return '成功';
            case 2:
                return '失败';
            case 3:
                return '未知';
        }

        return $result_status;
    }

    /**
     * 删除indexnow的密钥文件
     *
     * @param bool $delete_current 是否删除当前密钥文件
     *
     * @return void
     */
    public static function delete_indexnow_keyfile($delete_current = false)
    {
        global $ggpush_options;
        $ggpush_files = glob(ABSPATH . "ggpush-*.txt");
        $ggpush_current_file = '';
        if (!empty($ggpush_options['indexnow_token'])) {
            $ggpush_current_file = 'ggpush-' . $ggpush_options['indexnow_token'] . '.txt';
        } else {
            // 没有配置或者配置为空，需要删除
            $delete_current = true;
        }
        if (!empty($ggpush_files)) {
            foreach ($ggpush_files as $ggpush_file) {
                if (!$delete_current) {
                    if (false !== stripos($ggpush_file, $ggpush_current_file)) {
                        continue;
                    }
                }
                @unlink($ggpush_file);
            }
        }
    }

    /**
     * 获取GET中的参数值
     *
     * @param $name
     * @param $defValue
     *
     * @return mixed
     */
    public static function get($name, $defValue = '')
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }

        return $defValue;
    }
}