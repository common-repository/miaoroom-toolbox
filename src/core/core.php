<?php

require_once 'constants.php';

use MRTB\loaders\MainLoader;

if ( ! function_exists( 'mrtb_get_option' ) ) {
	/**
	 * @param string $option
	 * @param null $default
	 *
	 * @return |null
	 */
	function mrtb_get_option( $option = '', $default = null ) {
		$options = get_option( 'MRTB' ); // Attention: Set your unique id of the framework

		return ( isset( $options[ $option ] ) ) ? $options[ $option ] : $default;
	}
}

/**
 * 定时周期选项
 */
add_filter( 'cron_schedules', 'mrtb_cron_add_every_minutes' );
function mrtb_cron_add_every_minutes( $schedules ) {
	$schedules['every_minutes'] = array(
		'interval' => 60,
		'display'  => __( 'per minute', 'mrtb' )
	);

	return $schedules;
}

add_filter( 'cron_schedules', 'mrtb_cron_half_day' );
function mrtb_cron_half_day( $schedules ) {
	$schedules['half_day'] = array(
		'interval' => 43200,
		'display'  => __( 'Half a day', 'mrtb' )
	);

	return $schedules;
}

new MainLoader();

add_filter( 'plugin_action_links', function ( $links, $file ) {

	if ( strpos( $file, 'miaoroom-toolbox' ) !== false ) {
		$settings_link = '<a href="/wp-admin/admin.php?page=miaoroom-toolbox-settings#tab=1">' . __( "settings", "mrtb" ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}


	return $links;
}, 10, 2 );

date_default_timezone_set( get_option('timezone_string'));


if ( ! function_exists( 'mr_txt' ) ) {
	function mr_txt() {
		return '更多精品WP资源尽在<a href="https://www.miaoroom.com/">喵容</a>';
	}

	add_filter( 'admin_footer_text', 'mr_txt', 9999 );
}