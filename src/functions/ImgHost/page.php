<?php


add_action( 'parse_request', 'img_host_compair' );

function img_host_compair() {
	if ( $_SERVER["REQUEST_URI"] == '/mrtb/img_host_compair' && current_user_can( 'administrator' ) ) {
		?>
        <title>喵容聚合图床速度对比测试</title>
        <h1>请在浏览器禁用缓存后再打开，或从无痕窗口打开。</h1>
        <h2>蛋壳图床</h2>
        <img style="width: 20%;height: 20%;" src="https://imgkr.cn-bj.ufileos.com/702470b3-b987-477f-aa61-619edde26e37.png"/>
        <h2>Prnt</h2>
        <img style="width: 20%;height: 20%;" src="https://image.prntscr.com/image/G3XqKUnXQGO5ad-bSGDgFw.jpg"/>
        <h2>阿里巴巴</h2>
        <img style="width: 20%;height: 20%;" src="https://ae01.alicdn.com/kf/H21c7ba14fd4c42ce8977c3a99dd43b8b7.jpg"/>
        <h2>头条</h2>
        <img style="width: 20%;height: 20%;" src="http://p2.pstatp.com/origin/ff1d00023c4d2e8162e3"/>
		<?php
		exit();
	}
}

add_action( 'parse_request', 'img_host_check' );
function img_host_check() {
	if ( $_SERVER["REQUEST_URI"] == '/mrtb/img_host_check' && current_user_can( 'administrator' ) ) {
		?>
        <title>喵容聚合图床可用性检测</title>
        <h1>图床检测</h1>
        <hr/>
        <h2>蛋壳图床</h2>
		<?php
		$instance = new \MRTB\functions\ImgHost\free\DanKe();
		$host_url = $instance->uploadImageHost( MRTB_DIR_ASSETS_IMAGE . 'miaoroom.jpg' );
		?>
		<?php if ( $host_url ): ?>
            正常：<br/><img src="<?php echo esc_html($host_url); ?>"/>
		<?php else: ?>
            <p>异常</p>
		<?php endif ?>
        <h2>Prnt</h2>
		<?php
		$instance = new \MRTB\functions\ImgHost\free\Prnt();
		$host_url = $instance->uploadImageHost( MRTB_DIR_ASSETS_IMAGE . 'miaoroom.jpg' );
		?>
		<?php if ( $host_url ): ?>
            正常：<br/><img src="<?php echo esc_html($host_url); ?>"/>
		<?php else: ?>
            <p>异常</p>
		<?php endif ?>
		<?php
//		echo '<h2>阿里巴巴</h2>';
//		echo '<h2>头条</h2>';
		exit();
	}
}

add_action( 'parse_request', 'img_host_upload_list' );

function img_host_upload_list() {
	if ( $_SERVER["REQUEST_URI"] == '/mrtb/img_host_list' && current_user_can( 'administrator' ) ) {
		global $wpdb;
		$prefix         = $wpdb->prefix;
		$img_host_table = $prefix . 'mrtb_img_host';
		$result         = $wpdb->get_results( "
		    SELECT id,raw_url,host_url, create_time
		    FROM $img_host_table
		    ORDER BY id DESC
		    Limit 500
	    " );
		?>
        <title>喵容图床上传记录</title>
        <table>
            <thead>
            <th style="padding: 10px">ID</th>
            <th style="padding: 10px">上传时间</th>
            <th style="padding: 10px">原图地址</th>
            <th style="padding: 10px">图床地址</th>
            <thead>
            <tbody>
			<?php
			foreach ( $result as $v ) {
				?>
                <tr>
					<?php
					echo '<td style="padding: 10px">' . esc_html($v->id) . '</td>';
					echo '<td style="padding: 10px">' . esc_html($v->create_time) . '</td>';
					echo '<td style="padding: 10px">' . esc_html($v->raw_url) . '</td>';
					echo '<td style="padding: 10px">' . esc_html($v->host_url) . '</td>';
					?>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>
		<?php
		exit();
	}
}

add_action( 'parse_request', 'mrtb_img_host_repair' );

function mrtb_img_host_repair() {
	if ( $_SERVER["REQUEST_URI"] == '/mrtb/img_host_repair' && current_user_can( 'administrator' ) ) {
		echo '该版本暂不支持';
		exit();
	}
}