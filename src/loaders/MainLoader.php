<?php


namespace MRTB\loaders;


class MainLoader {
	public function __construct() {
		MainLoader::load_admin( 'CSF/codestar-framework' );
		MainLoader::load_admin( 'CSF/config' );
		MainLoader::load_admin( 'CSF/options/metabox.config' );
		$this->init();
	}

	private function init() {
		new FuncLoader();
	}

	public static function load_admin( $file ) {
		require_once( MRTB_DIR . '/admin/' . $file . '.php' );
	}

	public static function load_src( $file ) {
		require_once( MRTB_DIR . '/src/' . $file . '.php' );
	}

	public static function load_loader( $file ) {
		require_once( MRTB_DIR . '/src/loaders/' . $file . '.php' );
	}

	public static function load_function( $file ) {
		require_once( MRTB_DIR . '/src/functions/' . $file . '.php' );
	}

}