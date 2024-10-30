<?php


namespace MRTB\utils;


class Validate {
	public static function isUrl ($url){
		if(filter_var( $url, FILTER_VALIDATE_URL )){
			return true;
		}
		return false;
	}
}