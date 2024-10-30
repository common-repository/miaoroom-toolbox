<?php


namespace MRTB\functions\ImgHost\api;

use MRTB\utils\Requests;
use Psr\Http\Message\ResponseInterface;


abstract class ImgHostAbstract {

	const USER_AGENT = 'Mozilla/5.0 (Linux; Android 8.1; EML-AL00 Build/HUAWEIEML-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/53.0.2785.143 Crosswalk/24.53.595.0 XWEB/358 MMWEBSDK/23 Mobile Safari/537.36 MicroMessenger/6.7.2.1340(0x2607023A) NetType/4G Language/zh_CN';

	public function __construct() {
	}

	protected $API;

	protected $b = array(
		'JPEG' => "\xFF\xD8\xFF",
		'GIF'  => "GIF",
		'PNG'  => "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a",
		'BMP'  => 'BM',
	);

	protected $c;

	protected abstract function spadix( $response );


	public $type;

	protected $d;

	protected $e;


	protected abstract function httpConfig( $file );

	public static function local2Cdn( $url ) {
		$cdn_url = mrtb_get_option( 'image_host_cdn_origin' );
		if ( $cdn_url ) {
			return str_replace( get_site_url(), trailingslashit( $cdn_url ), $url );
		}

		return $url;
	}

	public static function cdn2Local( $url ) {
		$cdn_url = mrtb_get_option( 'image_host_cdn_origin' );
		if ( $cdn_url ) {
			return str_replace( trailingslashit( $cdn_url ), get_option( 'siteurl' ) . '/', $url );
		}

		return $url;
	}


	protected function fakeFileName() {
		return uniqid() . '.jpg';
	}


	protected function randomIP() {
		$ip_long  = array(
			array( '607649792', '608174079' ), //36.56.0.0-36.63.255.255
			array( '975044608', '977272831' ), //58.30.0.0-58.63.255.255
			array( '999751680', '999784447' ), //59.151.0.0-59.151.127.255
			array( '1019346944', '1019478015' ), //60.194.0.0-60.195.255.255
			array( '1038614528', '1039007743' ), //61.232.0.0-61.237.255.255
			array( '1783627776', '1784676351' ), //106.80.0.0-106.95.255.255
			array( '1947009024', '1947074559' ), //116.13.0.0-116.13.255.255
			array( '1987051520', '1988034559' ), //118.112.0.0-118.126.255.255
			array( '2035023872', '2035154943' ), //121.76.0.0-121.77.255.255
			array( '2078801920', '2079064063' ), //123.232.0.0-123.235.255.255
			array( '-1950089216', '-1948778497' ), //139.196.0.0-139.215.255.255
			array( '-1425539072', '-1425014785' ), //171.8.0.0-171.15.255.255
			array( '-1236271104', '-1235419137' ), //182.80.0.0-182.92.255.255
			array( '-770113536', '-768606209' ), //210.25.0.0-210.47.255.255
			array( '-569376768', '-564133889' ), //222.16.0.0-222.95.255.255
		);
		$rand_key = mt_rand( 0, 14 );

		return long2ip( mt_rand( $ip_long[ $rand_key ][0], $ip_long[ $rand_key ][1] ) );
	}


	protected function isFail( $response ) {
		if ( $response === false || $response->getStatusCode() !== 200 ) {
			return true;
		} else {
			return false;
		}
	}

	protected function isImage( $file ) {
		foreach ( $this->b as $type => $bit ) {
			if ( substr( $file, 0, strlen( $bit ) ) === $bit ) {
				return true;
			}
		}

		return false;
	}

	protected function validateImage( $file ) {

		if ( ! $file ) {
			return false;
		} else if ( ! $this->isImage( $file ) ) {
			return false;
		} else if ( $this->c && strlen( $file ) > $this->c ) {
			return false;
		} else {
			return true;
		}
	}

	protected function getFileFromLocal( $path ) {
		$file = false;

		if ( strpos( $path, '?' ) === false ) {
			$local_relative_path = substr( $path, strpos( $path, "wp-content" ) + strlen( 'wp-content' ) );
			$local_abs_path      = str_replace( '\\', '/', WP_CONTENT_DIR . $local_relative_path );
			$file                = @file_get_contents( $local_abs_path );
		}

		return $file;
	}

	protected function getFileFromRemote( $url, $isCdn = false ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}
		if ( $isCdn === false ) {
			$response = Requests::get( $url );

			return ( $response === false || $response->getStatusCode() !== 200 )
				? $this->getFileFromRemote( self::local2Cdn( $url ), true )
				: $response->getBody()->getContents();
		} else if ( $isCdn === true ) {
			$response = Requests::get( self::local2Cdn( $url ) );

			return ( $response === false || $response->getStatusCode() !== 200 ) ? false : $response->getBody()->getContents();
		}
	}

	protected function getImageFile( $url ) {
		$file = '';
		if ( strpos( $url, "wp-content" ) !== false ) {
			$file = $this->getFileFromLocal( $url );
		}
		if ( ! isset( $file ) || ! $this->isImage( $file ) ) {
			$file = $this->getFileFromRemote( $url );
		}

		return $file ? $file : false;
	}


	/**
	 * @param string $raw_url
	 * @param integer $post_id
	 *
	 * @return bool
	 */
	public function uploadImageHost( $raw_url = '' ) {
		if ( ! $raw_url ) {
			return false;
		}

		// 如果不支持gif格式
		if ( ! $this->e && stripos( $raw_url, '.gif' ) !== false ) {
			return false;
		}

		$file = $this->getImageFile( $raw_url );
		if ( $this->validateImage( $file ) ) {
			$promise = Requests::postAsync( $this->API, $this->httpConfig( $file ) )
			                   ->then(
				                   function ( $res ) {
					                   return $this->spadix( $res );
				                   } );

			return $promise->wait();
//			$response = @Requests::post( $this->API, $this->httpConfig( $file ) );
//			return @json_decode( $response->getBody()->getContents() );
		}

		return false;
	}


	public function insert( $post_id, $host_type, $raw_url, $host_url ) {
		global $wpdb;
		$prefix         = $wpdb->prefix;
		$img_host_table = $prefix . 'mrtb_img_host';
		$insert         = $wpdb->insert(
			$img_host_table,
			array(
				'post_id'     => $post_id,
				'host_type'   => $host_type,
				'raw_url'     => $raw_url,
				'host_url'    => $host_url,
				'create_time' => current_time( 'mysql' )
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		return $insert;
	}
}