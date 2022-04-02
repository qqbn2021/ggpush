<?php

/**
 * 推送类
 */
class Ggpush {

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
	public function push( array $urls, string $site, string $token, string $type = '' ) {
		$params = [
			'site'  => $site,
			'token' => $token
		];
		if ( ! empty( $type ) ) {
			$params['type'] = $type;
		}
		$apiUrl = 'http://data.zz.baidu.com/urls?' . http_build_query( $params );

		return wp_remote_post( $apiUrl, [
			'body'    => implode( "\n", $urls ),
			'headers' => [
				'Content-Type' => 'text/plain'
			]
		] );
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
	public function bingPush( array $urls, string $site, string $token ) {
		return wp_remote_post( 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey=' . $token, [
			'body'    => json_encode( [
				'siteUrl' => $site,
				'urlList' => $urls
			] ),
			'headers' => [
				'Content-Type' => 'application/json; charset=utf-8'
			]
		] );
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
	public function indexNowPush( array $urls, string $host, string $key, string $keyLocation, string $searchengine ) {
		$data = [
			'host'        => $host,
			'key'         => $key,
			'keyLocation' => $keyLocation,
			'urlList'     => $urls
		];

		return wp_remote_post( 'https://' . $searchengine . '/indexnow', [
			'body'    => json_encode( $data ),
			'headers' => [
				'Host'         => $searchengine,
				'Content-Type' => 'application/json; charset=utf-8'
			]
		] );
	}
}