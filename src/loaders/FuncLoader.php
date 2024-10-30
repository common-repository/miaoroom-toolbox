<?php


namespace MRTB\loaders;


class FuncLoader {
	public function __construct() {
		MainLoader::load_function( 'optimizeFunc' );
		if ( mrtb_get_option( 'image_host_switch' ) ) {
			MainLoader::load_function( 'ImgHost/imgHostFunc' );
		}
	}
}