<?php
/*
	Plugin Name: Miaoroom ToolBox
	Plugin URI: https://www.miaoroom.com/course/wordpress-plugin/miaoroom-toolbox.html
	Description: 喵容工具箱提供诸多实用功能，高速聚合图床外链加速图片可以极大节省CDN费用，以及诸多优化网站设置，赋能中小企业与个人站长。
	Author: miaoroom
	Author URI: https://www.miaoroom.com/
	Tags: 喵容,miaoroom,聚合图床,图床,CDN图床,外链图床,建站工具,性能优化,权限优化,建站工具,优化工具,WordPress 优化,性能优化,速度优化,喵容工具箱,喵容图床,喵容聚合图床
	Version: 1.0
	Text Domain: mrtb
*/

require 'vendor/autoload.php';
require_once(dirname(__FILE__) . '/src/core/core.php');

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function mrtb_load_plugin_textdomain() {
	load_plugin_textdomain( 'mrtb', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'mrtb_load_plugin_textdomain' );