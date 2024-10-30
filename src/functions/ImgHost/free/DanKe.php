<?php


namespace MRTB\functions\ImgHost\free;

use MRTB\functions\ImgHost\api\ImgHostAbstract;
use MRTB\utils\Requests;

class DanKe extends ImgHostAbstract {

	protected $API = 'https://imgkr.com/api/files/upload';

	protected $c = 5242880;

	public $type = 'DanKe';

	public $d = false;

	protected $e = true;

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
					'name'     => 'file',
					'contents' => $file,
					'filename' => $fileName
				],
			]
		];
	}


	protected function spadix( $response ) {
		$obj      = @json_decode( $response->getBody()->getContents() );
		$host_url = $obj->success ? $obj->data : false;
		return $host_url;
	}
}