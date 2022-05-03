<?php

/**
 * 推送类
 */
class Ggpush
{
    /**
     * 获取请求超时时间
     * @return int
     */
    public static function get_request_timeout()
    {
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_push_timeout'])) {
            return intval($options['ggpush_push_timeout']);
        }
        return 30;
    }

    /**
     * 百度站长平台推送
     *
     * @param array $urls 推送网址
     * @param string $site 推送网站
     * @param string $token 推送token
     * @param string $type 为daily则为快速推送
     *
     * @return array|WP_Error
     */
    public static function push($urls, $site, $token, $type = '')
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
            'timeout' => self::get_request_timeout(),
            'headers' => array(
                'Content-Type' => 'text/plain'
            )
        ));
    }

    /**
     * bing搜索引擎推送
     *
     * @param array $urls 推送网址
     * @param string $site 推送站点
     * @param string $token 推送apikey
     *
     * @return array|WP_Error
     */
    public static function bing_push($urls, $site, $token)
    {
        return wp_remote_post('https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey=' . $token, array(
            'body' => json_encode(array(
                'siteUrl' => $site,
                'urlList' => $urls
            )),
            'timeout' => self::get_request_timeout(),
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            )
        ));
    }

    /**
     * indexnow推送
     *
     * @param array $urls 推送网址
     * @param string $host 推送域名
     * @param string $key 推送key
     * @param string $keyLocation 推送key文本文件位置
     * @param string $searchengine 推送的搜索引擎，api.indexnow.org、www.bing.com、yandex.com
     *
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
            'timeout' => self::get_request_timeout(),
            'headers' => array(
                'Host' => $searchengine,
                'Content-Type' => 'application/json; charset=utf-8'
            )
        ));
    }
}