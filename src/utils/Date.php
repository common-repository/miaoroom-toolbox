<?php


namespace MRTB\utils;


use DateTime;

class Date {

	/**
	 * @param $o int
	 * @param $n int
	 * @param $format string
	 *
	 * @return int
	 */
	public static function Diff( $o, $n, $format ) {
		$old  = new DateTime( date( "Y/m/d H:i:s", $o ) );
		$new  = new DateTime( date( "Y/m/d H:i:s", $n ) );
		$diff = $old->diff( $new );
		switch ( $format ) {
			case ( $format === 'year' || $format === 'y' ):
				return $diff->y;
				break;
			case ( $format === 'month' || $format === 'm' ):
				return $diff->m;
				break;
			case ( $format === 'day' || $format === 'd' ):
				return $diff->d;
				break;
			case ( $format === 'hour' || $format === 'h' ):
				return $diff->h;
				break;
			case ( $format === 'minute' || $format === 'i' ):
				return $diff->i;
				break;
		}
	}
}