<?php

/**
 * 推送核心处理类
 */
class Ggpush_Api
{

    /**
     * 获取请求超时时间
     * @return int
     */
    public static function get_request_timeout()
    {
        return Ggpush_Plugin::get_option('push_timeout', 30);
    }

    /**
     * 格式化推送平台
     * @param int $record_platform
     * @return string
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
     * @param int $record_mode
     * @return string
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
     * @param int $result_status
     * @return string
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
     * 查询指定字段的文章数据
     * @param string $field 查询字段
     * @param int $num 查询数量
     * @param int $type 1 最新 2 随机 3 伪随机
     * @param int $offset 过滤多少条数据
     * @return array
     */
    public static function get_post_data($field, $num, $type, $offset = 0)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';
        $order = 'DESC';
        $orderby = 'ID';
        $where = '';
        if (2 == $type) {
            $orderby = 'rand()';
            $order = '';
        } else if (3 == $type) {
            $max_id = $wpdb->get_var('SELECT MAX(ID) FROM `' . $table_name . '` where `post_status` = "publish"');
            $order = 'ASC';
            if ($max_id > $num) {
                $start_id = mt_rand(0, $max_id - $num);
                $where = '`ID` > ' . $start_id . ' AND';
            }
        }
        $sql = 'SELECT ' . $field . ' FROM `' . $table_name . '` WHERE ' . $where . ' `post_status` = %s ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d, %d';
        $query = $wpdb->prepare(
            $sql,
            'publish',
            $offset,
            $num
        );
        $results = $wpdb->get_results($query, ARRAY_A);
        $data = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $data[] = $result;
            }
        }
        return $data;
    }

    /**
     * 获取推送文章网址
     * @param int $num 推送数量
     * @param int $type 1 最新 2 随机 3 伪随机
     * @param int $offset 过滤多少条数据
     * @return array
     */
    public static function get_post_url($num, $type, $offset = 0)
    {
        $data = self::get_post_data('ID', $num, $type, $offset);
        $urls = array();
        if (!empty($data)) {
            foreach ($data as $v) {
                $urls[] = get_permalink($v['ID']);
            }
        }
        return $urls;
    }

    /**
     * 获取推送文章网址和推送时间
     * @param int $num 推送数据
     * @param int $type 1 最新 2 随机 3 伪随机
     * @return array
     */
    public static function get_post_url_and_date($num, $type)
    {
        $data = self::get_post_data('ID,post_date', $num, $type);
        $result = array();
        if (!empty($data)) {
            foreach ($data as $v) {
                $result[] = array(
                    'url' => get_permalink($v['ID']),
                    'date' => date('Y-m-d', strtotime($v['post_date']))
                );
            }
        }
        return $result;
    }

    /**
     * 保存推送日志记录
     * @param $data
     * @return bool|int|mysqli_result|resource|null
     */
    public static function save_record($data)
    {
        if (empty($data)) {
            return false;
        }
        // 不保存推送记录
        $push_record = Ggpush_Plugin::get_option('push_record', 0);
        if (1 == $push_record) {
            return true;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';
        return $wpdb->insert($table_name, $data);
    }

    /**
     * 链接多平台推送
     * @param string|array $url
     * @param array|string|int $platforms
     * @return bool|array
     */
    public static function platform_push($url, $platforms)
    {
        if (empty($url) || empty($platforms)) {
            Ggpush_Plugin::set_error('链接为空或推送平台为空');
            return false;
        }
        if (is_string($url)) {
            $url = array($url);
        }
        if (is_numeric($platforms)) {
            $platforms = array($platforms);
        }
        $result = array();
        foreach ($platforms as $platform) {
            $result[$platform] = self::single_platform_push($url, $platform);
        }
        return $result;
    }

    /**
     * 链接多平台推送
     * @param string|array $url
     * @param string|int $platform
     * @return bool
     */
    public static function single_platform_push($url, $platform)
    {
        if (empty($url) || empty($platform)) {
            Ggpush_Plugin::set_error('链接为空或推送平台为空');
            return false;
        }
        if (is_string($url)) {
            $url = array($url);
        }
        switch ($platform) {
            case 1:
                // 百度搜索引擎普通收录
                return self::push_baidu($url);
            case 2:
                // 百度搜索引擎快速收录
                return self::push_baidu($url, true);
            case 4:
                // 必应搜索引擎
                return self::push_bing($url);
            case 7:
                // IndexNow
                return self::push_indexnow($url);
        }
        return false;
    }

    /**
     * 百度站长平台推送
     * @param array $urls 推送网址
     * @param string $site 推送网站
     * @param string $token 推送token
     * @param string $type 为daily则为快速推送
     * @return array|WP_Error
     */
    public static function baidu_push($urls, $site, $token, $type = '')
    {
        $params = array(
            'site' => $site,
            'token' => $token
        );
        if (!empty($type)) {
            $params['type'] = $type;
        }
        $apiUrl = 'http://data.zz.baidu.com/urls?' . http_build_query($params);
        return wp_remote_post($apiUrl, array(
            'body' => implode("\n", $urls),
            'sslverify' => false,
            'timeout' => self::get_request_timeout(),
            'headers' => array(
                'Content-Type' => 'text/plain'
            )
        ));
    }

    /**
     * 百度推送
     * @param array $urls
     * @param bool $daily
     * @return bool|int|mysqli_result|resource|null
     */
    public static function push_baidu($urls, $daily = false)
    {
        $baidu_token = Ggpush_Plugin::get_option('baidu_token');
        if (empty($baidu_token)) {
            Ggpush_Plugin::set_error('未设置百度推送');
            return false;
        }
        $response = self::baidu_push($urls, parse_url(get_home_url(), PHP_URL_HOST), $baidu_token, $daily ? 'daily' : '');
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
        Ggpush_Plugin::set_error($record_result_error);
        return self::save_record($data);
    }

    /**
     * bing搜索引擎推送
     * @param array $urls 推送网址
     * @param string $site 推送站点
     * @param string $token 推送apikey
     * @return array|WP_Error
     */
    public static function bing_push($urls, $site, $token)
    {
        return wp_remote_post('https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey=' . $token, array(
            'body' => json_encode(array(
                'siteUrl' => $site,
                'urlList' => $urls
            )),
            'sslverify' => false,
            'timeout' => self::get_request_timeout(),
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            )
        ));
    }

    /**
     * 推送链接到bing
     * @param array $urls
     * @return bool|int|mysqli_result|resource|null
     */
    public static function push_bing($urls)
    {
        $bing_token = Ggpush_Plugin::get_option('bing_token');
        if (empty($bing_token)) {
            Ggpush_Plugin::set_error('未设置必应推送');
            return false;
        }
        $response = self::bing_push($urls, get_home_url(), $bing_token);
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
        Ggpush_Plugin::set_error($record_result_error);
        return self::save_record($data);
    }

    /**
     * indexnow推送
     * @param array $urls 推送网址
     * @param string $host 推送域名
     * @param string $key 推送key
     * @param string $keyLocation 推送key文本文件位置
     * @param string $searchengine 推送的搜索引擎，api.indexnow.org、www.bing.com、yandex.com
     * @return array|WP_Error
     */
    public static function index_now_push($urls, $host, $key, $keyLocation, $searchengine)
    {
        $data = array(
            'host' => $host,
            'key' => $key,
            'keyLocation' => $keyLocation,
            'urlList' => $urls
        );
        return wp_remote_post('https://' . $searchengine . '/indexnow', array(
            'body' => json_encode($data),
            'sslverify' => false,
            'timeout' => self::get_request_timeout(),
            'headers' => array(
                'Host' => $searchengine,
                'Content-Type' => 'application/json; charset=utf-8'
            )
        ));
    }

    /**
     * 推送链接到indexnow
     * @param array $urls
     * @return bool
     */
    public static function push_indexnow($urls)
    {
        $indexnow_token = Ggpush_Plugin::get_option('indexnow_token');
        if (empty($indexnow_token) || !file_exists(ABSPATH . $indexnow_token . '.txt')) {
            Ggpush_Plugin::set_error('未设置IndexNow推送');
            return false;
        }
        $keyLocation = get_home_url() . '/' . $indexnow_token . '.txt';
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
            $response = self::index_now_push($urls, parse_url(get_home_url(), PHP_URL_HOST), Ggpush_Plugin::get_option('indexnow_token'), $keyLocation, $host);
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
                } else if ($record_result_code === 202) {
                    $record_result_status = 3;
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
            if (!empty($record_result_error)) {
                Ggpush_Plugin::set_error($record_result_error);
            }
            self::save_record($data);
        }
        return true;
    }

    /**
     * 更新indexnow密钥验证文件
     * @param string $newfile 新的密钥文件
     * @param string $oldfile 旧的密钥文件
     * @return bool|int
     */
    public static function update_indexnow_keyfile($newfile, $oldfile = '')
    {
        if (!empty($oldfile) && file_exists(ABSPATH . $oldfile . '.txt')) {
            @unlink(ABSPATH . $oldfile . '.txt');
        }
        if (!empty($newfile)) {
            return file_put_contents(ABSPATH . $newfile . '.txt', $newfile);
        }
        return true;
    }

    /**
     * 删除indexnow的密钥文件
     * @return void
     */
    public static function delete_indexnow_keyfile()
    {
        global $ggpush_options;
        if (!empty($ggpush_options['indexnow_token'])) {
            @unlink(ABSPATH . $ggpush_options['indexnow_token'] . '.txt');
        }
    }
}