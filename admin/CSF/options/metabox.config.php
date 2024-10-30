<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // 更多精品WP资源尽在喵容：miaoroom.com


//$prefix_post_metabox_style = 'mrtb-postmeta-box';
$prefix = 'mrtb';

$types = mrtb_get_option( 'image_host_post_type' );
if (mrtb_get_option( 'image_host_switch' ) && isset($types) && gettype(mrtb_get_option( 'image_host_post_type' )) === 'array' ) {
	$v = array_values($types);
	CSF::createMetabox( $prefix, array(
		'title'     => '是否启用图床加速',
		'post_type' => $v,
		'data_type' => 'unserialize',
		'context'   => 'side',
	) );
	CSF::createSection( $prefix, array(
		'fields' => array(
			array(
				'id'      => 'post_image_host_switch',
				'type'    => 'switcher',
// 更多精品WP资源尽在喵容：miaoroom.com
				'inline'  => true,
// 更多精品WP资源尽在喵容：miaoroom.com
// 更多精品WP资源尽在喵容：miaoroom.com
// 更多精品WP资源尽在喵容：miaoroom.com
				'default' => false,
			),
		),
	) );
}
