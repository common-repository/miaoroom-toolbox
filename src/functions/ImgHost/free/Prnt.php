<?php


namespace MRTB\functions\ImgHost\free;


use MRTB\functions\ImgHost\api\ImgHostAbstract;
use MRTB\utils\Requests;

class Prnt extends ImgHostAbstract {

	protected $API = 'https://prntscr.com/upload.php';

	protected $sizeLimit = 5242880;

	protected $canGif = true;

	public $canWebp = false;

	public $type = 'Prnt';

	protected function httpConfig( $file ) {
		$fileName = $this->fakeFileName();
		$randomIP = $this->randomIP();

		return [
			'curl.options'    => [
				'CURLOPT_VERIFYPEER' => false,
				'CURLOPT_HTTPHEADER' => array(
					'X-FORWARDED-FOR' => $randomIP,
					'x-host-ip'       => $randomIP
				)
			],
			'headers'         => [
				'User-Agent'      => self::USER_AGENT,
				'Accept'          => 'application/json',
				'X-FORWARDED-FOR' => $randomIP,
				'x-host-ip'       => $randomIP
			],
			'connect_timeout' => 900,
			'timeout'         => 900,
			'verify'          => false,
			'multipart'       => [
				[
					'name'     => 'image',
					'contents' => $file,
					'filename' => $fileName
				],
			]
		];
	}

	protected function spadix( $response ) {

		if ( $this->isFail( $response ) ) {
			return false;
		}
		$data = @json_decode( $response->getBody()->getContents() );
		if ( $data->status !== 'success' ) {
			return false;
		}

		$response = @Requests::post( $data->data, [
			'headers' => [
				'User-Agent' => self::USER_AGENT,
				'Accept'     => '*/*',
			],
			'verify'  => false,
		] );

		if ( $this->isFail( $response ) ) {
			return false;
		}
		$html = $response->getBody()->getContents();
		$reg  = '/<img.*? src="([^"]+)" .*?crossorigin="anonymous"/';
		if ( preg_match( $reg, $html, $mx ) ) {
			$host_url = $mx[1];
		}

		if ( ! isset( $host_url ) ) {
			return false;
		}

		Requests::head( $host_url );

		return $host_url;

	}
}