<?php if ( ! defined( 'ABSPATH' ) ) {
	/*
     * @Description:
     * @version:
     * @Author: znnnnn
     * @Date: 2019-08-11 20:37:52
 * @LastEditors: znnnnn
 * @LastEditTime: 2019-09-01 23:00:45
     */
	die;
} // 更多精品WP资源尽在喵容：miaoroom.com
//
// 更多精品WP资源尽在喵容：miaoroom.com
//
$prefix = 'MRTB';
//
// 更多精品WP资源尽在喵容：miaoroom.com
//
CSF::createOptions( $prefix, array(
	'menu_title' => '喵容工具箱设置',
	'menu_slug'  => 'miaoroom-toolbox-settings',
) );

CSF::createSection( $prefix, array(
	'id'          => 'image_host_settings',
	'title'       => '喵容聚合图床设置',
	'description' => '
<h3 style="margin: 0">1. 不是重要或隐私图片，使用本功能可以帮您省去大量CDN费用，使用公共图床加速站内图片来极大加快图片加载速度。注意：公共图床不保证图片安全性，本地必须保留备份图片。</h3><br/>
<h3 style="margin: 0">2. 本插件严禁使用者上传非法图片至图床，上传的图片均在<span style="color: red">公共服务器（非作者服务器）</span>且无法删除，本插件仅做技术交流，请合法使用，由此产生的一切后果均与插件作者无关。</h3><br/>
<h3 style="margin: 0">3. 使用本插件的图床功能前建议将<span style="color: red">PHP的超时时间设置为86400</span>，否则数据量大了容易执行超时报错。</h3><br/>
<p style="margin: 0">4. 本插件目前完美支持主题：<code>B2全站缩略图</code>和插件：<code>Oss Upload（自动禁用webp）</code>，其他主题暂不支持。（若要支持需要主题支持提供站外缩略图接口）</p>
<p style="margin: 0">5. <span style="color:red">第一次开启图床会占用带宽传图导致很卡</span>，待图片上传完毕后就没事，可以前往<strong>域名/mrtb/img_host_list</strong>查看目前上传记录</p>
<hr />
<p style="margin: 0">1. Pro版本支持更高级电商图床（更稳定）、webp图床、图床定期扫描修复等高级实用功能，<a href="https://www.miaoroom.com/course/wordpress-plugin/miaoroom-toolbox.html">了解Pro版本</a></p>
<p style="margin: 0">2. 有Bug可前往<a href="https://www.miaoroom.com/course/wordpress-plugin/miaoroom-toolbox.html">插件官网</a>进行反馈</p>
<p style="margin: 0">3. 有自建图床或其他定制需求可前往<a href="https://www.miaoroom.com/course/wordpress-plugin/miaoroom-toolbox.html">留言板</a>进行留言</p>',
	'icon'        => 'fa fa-home fa-fw',
	'fields'      => array(
		array(
			'type'    => 'notice',
			'style'   => 'success',
			'content' => '<a href="/mrtb/img_host_list" target="_blank">上传记录（最近500条）</a> | <a href="/mrtb/img_host_check" target="_blank">图床检测</a> | <a href="/mrtb/img_host_compair" target="_blank">图床速度对比</a> | 手动<a target="_blank" href="/mrtb/img_host_repair">图床修复</a>',
		),
		array(
			'type'    => 'subheading',
			'content' => '功能优化',
		),
		array(
			'id'       => 'image_host_switch',
			'title'    => '图床开关',
			'subtitle' => '是否打开图床对WP进行加速，<strong style="color: red">第一次启用比较卡，PHP超时设置为86400</strong>',
			'type'     => 'switcher',
			'default'  => false
		),
		array(
			'id'       => 'image_host_which',
			'title'    => '聚合图床选择',
			'subtitle' => '<a href="/mrtb/img_host_compair" target="_blank">图床速度对比</a> | 聚合图床加速检索不分图床，注意图片上传要求为：小于5M,长或宽不得大于5000PX',
			'type'     => 'radio',
			'options'  => array(
				'DanKe' => '蛋壳（三方接口 | 不支持gif | 自动鉴黄）',
				'Prnt'    => 'Prnt（国内容易403，国外服务器可备用）',
				'Ali'     => '阿里巴巴（仅Pro版本支持）',
				'TouTiao' => '头条（仅版本支持）',
			),
			'default'  => 'DanKe'
		),
		array(
			'id'       => 'image_host_post_type',
			'type'     => 'checkbox',
			'title'    => '启用图床的文章类型',
			'subtitle' => '将文章内的图片替换为图床地址<strong>（第一次启用比较卡）</strong>',
			'options'  => 'post_types',
		),
		array(
			'id'          => 'image_host_cdn_origin',
			'type'        => 'text',
			'title'       => 'CDN地址',
			'subtitle'    => '如果你原先在本地未存图片，将所有图片存储在CDN，那么在下方填入CDN地址，请带上<code>http://</code>或<code>https://</code>',
			'default'     => '',
			'placeholder' => 'https://cdn.xxxxx.com',
			'validate'    => 'csf_validate_url',
		),
		array(
			'id'       => 'image_host_is_cache',
			'title'    => '图床数据缓存',
			'subtitle' => '强烈建议启用Redis或者Memcached，开启后极大加快图床查询速度。',
			'type'     => 'switcher',
			'default'  => false
		),
		array(
			'id'       => 'image_host_front_thumb',
			'title'    => '前台缩略图加速',
			'subtitle' => '极大加速前台文章缩略图，<strong>若主题使用了Timthumb等缩略图工具则效果不大</strong>（第一次启用比较卡）',
			'type'     => 'switcher',
			'default'  => false
		),
		array(
			'id'       => 'image_host_wp_crop',
			'title'    => '禁用WP自带裁剪图片',
			'subtitle' => '如果不禁用则可能前台缩略图异常',
			'type'     => 'switcher',
			'default'  => true
		),
		array(
			'id'       => 'image_host_url_param_switch',
			'title'    => '移除图片样式（仅Pro版本支持）',
			'subtitle' => '用于移除水印上传至图床，开启后http://xxx.jpg@!full 会移除末尾的 @!full',
			'type'     => 'button',
			'default'  => false
		),
		array(
			'id'       => 'image_host_webp_mode',
			'title'    => 'webp图床（仅Pro版本支持）',
			'subtitle' => '1.与<strong style="color: #000">Super Cache/WP Rocket等静态化插件</strong>不兼容，同时启用会导致不支持webp格式的浏览器显示异常！<br/>
							2. <strong style="color: #000">Timthumb缩略图工具</strong>不兼容webp格式！<br/>
							3.若选择了不支持webp的图床，则该选项无效。',
			'type'     => 'button',
			'default'  => false
		),
		array(
			'id'       => 'image_host_f_repair',
			'type'     => 'button',
			'title'    => '前台修复图床（仅Pro版本支持）',
			'subtitle' => '用户访问到失效的图片便立刻通知服务器修复对应图片，会略微降低前台速度。',
			'default'  => false,
		),
		array(
			'id'       => 'image_host_thumb',
			'title'    => '媒体库加速（测试功能谨慎开启）',
			'subtitle' => '极大加速媒体库缩略图（第一次启用比较卡）',
			'type'     => 'switcher',
			'default'  => false
		),
	),
) );


CSF::createSection( $prefix, array(
	'id'          => 'opt_settings',
	'title'       => '优化设置',
	'description' => '优化wordpress功能与权限，加快速度。',
	'icon'        => 'fa fa-home fa-fw',
	'fields'      => array(
// 更多精品WP资源尽在喵容：miaoroom.com
// 更多精品WP资源尽在喵容：miaoroom.com
// 更多精品WP资源尽在喵容：miaoroom.com
// 更多精品WP资源尽在喵容：miaoroom.com
		array(
			'type'    => 'subheading',
			'content' => '功能优化',
		),
		array(
			'id'       => 'opt_remove_categories_prefix',
			'title'    => '移除分类目录前缀',
			'subtitle' => '使固定链接去掉分类目录链接中的<code>category</code>或者自定义分类<code>%taxonomy%</code>，请先修改固定链接。',
			'type'     => 'checkbox',
			'default'  => false
		),
		array(
			'id'       => 'opt_admin_show_own_media',
			'title'    => '优化媒体库图片权限',
			'subtitle' => '普通用户（非管理员）在后台只显示自己上传的图片',
			'type'     => 'checkbox',
			'default'  => false
		),
		array(
			'id'       => 'opt_admin_show_own_post',
			'title'    => '优化后台文章列表',
			'subtitle' => '<strong>编辑权限以下的用户</strong>在后台只显示自己的文章',
			'type'     => 'checkbox',
			'default'  => false
		),
		array(
			'id'       => 'opt_admin_show_own_post_comment',
			'title'    => '优化后台评论列表',
			'subtitle' => '普通用户（非管理员）在后台只显示自己文章的评论',
			'type'     => 'checkbox',
			'default'  => false
		),

	),
) );