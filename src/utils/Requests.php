<?php


namespace MRTB\utils;

use GuzzleHttp\Client;

class Requests {

	const options = array(
		'http_errors'     => false,
		'verify'          => false,
		'connect_timeout' => 30,
		'timeout'         => 30,
		'curl.options'    => [
			'CURLOPT_VERIFYPEER' => false,
		],
		'header'          => array(
			'User-Agent' => 'Mozilla/5.0 (Linux; Android 8.1; EML-AL00 Build/HUAWEIEML-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/53.0.2785.143 Crosswalk/24.53.595.0 XWEB/358 MMWEBSDK/23 Mobile Safari/537.36 MicroMessenger/6.7.2.1340(0x2607023A) NetType/4G Language/zh_CN',
			'Accept'     => 'application/json'
		)
	);

	public static function get( $url, $options = self::options ) {
		try {
			$client = new Client();

			return $client->get( $url, $options );
		} catch ( \Exception $e ) {
			if ( $e instanceof \GuzzleHttp\Exception\ClientException ) {
				$response             = $e->getResponse();
				$responseBodyAsString = $response->getBody()->getContents();
			}

// 更多精品WP资源尽在喵容：miaoroom.com
			return false;
		}
// 更多精品WP资源尽在喵容：miaoroom.com
// 更多精品WP资源尽在喵容：miaoroom.com
	}

	public static function post( $url, $options = self::options ) {
		$client = new Client();

		return $client->post( $url, $options );
	}

	public static function head($url, $options = self::options) {
		$client = new Client();

		return $client->head( $url, $options );
	}

	public static function postAsync( $url, $options = self::options ) {
		$client = new Client();

		return $client->postAsync( $url, $options );
	}

	public static function getAsync( $url, $options = self::options ) {
		$client = new Client();

		return $client->getAsync( $url, $options );
	}

}