<?php
// 启用插件
function ggpush_plugin_activation() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'ggpush_records';
	$charset_collate = $wpdb->get_charset_collate();
	$sql             = <<<SQL
CREATE TABLE $table_name (
	`record_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_platform` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '推送平台：1 百度，2 360，3 搜狗，4 头条，5 神马，6 bing，7 谷歌，8 indexnow，9 yandex',
	`record_mode` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '推送方式：1 普通收录，2 快速收录，3 js提交，4 api提交，5 indexnow',
	`record_urls` LONGTEXT NULL DEFAULT NULL COMMENT '推送链接' COLLATE 'utf8mb4_general_ci',
	`record_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '推送链接数量',
	`record_result` TEXT NULL DEFAULT NULL COMMENT '推送结果' COLLATE 'utf8mb4_general_ci',
	`record_result_code` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '200' COMMENT '推送结果状态码',
	`record_result_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '推送状态：1 成功，2 失败，3 未知',
	`record_result_error` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '失败原因' COLLATE 'utf8mb4_general_ci',
	`record_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '推送时间',
	PRIMARY KEY (`record_id`) USING BTREE
) $charset_collate;
SQL;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//	dbDelta( $sql );
	// 如果表不存在才会执行创建
	maybe_create_table( $table_name, $sql );
	// 更新定时任务
	ggpush_create_baidu_cron();
	ggpush_create_baidu_fast_cron();
	ggpush_create_bing_cron();
	ggpush_create_indexnow_cron();
}

// 删除插件执行的代码
function ggpush_plugin_uninstall() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'ggpush_records';
	$wpdb->query( 'DROP TABLE IF EXISTS `' . $table_name . '`' );
}

// 禁用插件执行的代码
function ggpush_plugin_deactivation() {
	// 插件已禁用，删除定时任务
	ggpush_delete_baidu_cron();
	ggpush_delete_baidu_fast_cron();
	ggpush_delete_bing_cron();
	ggpush_delete_indexnow_cron();
}

/**
 * 设置说明
 */
function ggpush_section_callback() {
	echo sprintf( __( 'You need to monitor %s regularly to push links', 'ggpush' ), '<code>' . get_home_url() . '/wp-cron.php' . '</code>' );
}

/**
 * 表单输入框回调
 *
 * @param array $args 这数据就是add_settings_field方法中第6个参数（$args）的数据
 */
function ggpush_field_callback( $args ) {
	// 表单的id或name字段
	$id = $args['label_for'];
	// 表单的类型
	$form_type = $args['form_type'] ?? 'input';
	// 输入表单说明
	$form_desc = $args['form_desc'] ?? '';
	// 输入表单type
	$type = $args['type'] ?? 'text';
	// 输入表单placeholder
	$form_placeholder = $args['form_placeholder'] ?? '';
	// 下拉框等选项值
	$form_data = $args['form_data'] ?? [];
	// 表单的名称
	$input_name = 'ggpush_options[' . $id . ']';
	// 获取表单选项中的值
	$options = get_option( 'ggpush_options' );
	// 表单的值
	$input_value = $options[ $id ] ?? '';
	if ( empty( $input_value ) && 'ggpush_indexnow_token' === $id ) {
		// 随机生成indexnow token
		$input_value = md5( get_home_url() . date( 'Y-m-d H:i:s' ) . mt_rand( 1000, 9999 ) );
	}
	$form_html = '';
	switch ( $form_type ) {
		case 'input':
			$form_html = '<input id="' . $id . '" type="' . $type . '" placeholder="' . esc_html( $form_placeholder ) . '" name="' . $input_name . '" value="' . esc_html( $input_value ) . '" class="regular-text">';
			break;
		case 'select':
			$select_options = '';
			foreach ( $form_data as $v ) {
				$selected = '';
				if ( $v['value'] == $input_value ) {
					$selected = 'selected="selected"';
				}
				$select_options .= '<option ' . $selected . ' value="' . $v['value'] . '">' . $v['title'] . '</option>';
			}
			$form_html = '<select id="' . $id . '" name="' . $input_name . '">' . $select_options . '</select>';
			break;
		case 'checkbox':
			$checkbox_options = '<fieldset><p>';
			$len              = count( $form_data );
			foreach ( $form_data as $k => $v ) {
				$checked = '';
				if ( in_array( $v['value'], $input_value ) ) {
					$checked = 'checked="checked"';
				}
				$checkbox_options .= '<label><input type="checkbox" value="' . $v['value'] . '" id="' . $id . '_' . $v['value'] . '" name="' . $input_name . '[]" ' . $checked . '>' . $v['title'] . '</label>';
				if ( $k < ( $len - 1 ) ) {
					$checkbox_options .= '<br>';
				}
			}
			$form_html = $checkbox_options . '</p></fieldset>';
			break;
	}
	if ( ! empty( $form_desc ) ) {
		$form_html .= '<p class="description">' . esc_html( $form_desc ) . '</p>';
	}
	echo $form_html;
}

/**
 * 初始化页面
 */
function ggpush_page_init() {
	// 注册一个新页面
	register_setting( 'ggpush', 'ggpush_options' );

	// 在新的设置页面添加表单说明文字
	add_settings_section(
		'ggpush_cron_section',
		__( 'Push Links via WP-Cron', 'ggpush' ),
		'ggpush_section_callback',
		'ggpush'
	);

	// 百度搜索引擎普通收录
	add_settings_section(
		'ggpush_section',
		__( 'Baidu search engine general collection', 'ggpush' ),
		null,
		'ggpush'
	);

	// 在新的设置页面添加表单输入框
	add_settings_field(
		'ggpush_baidu_token',
		// 输入框说明文字
		__( 'Access token', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section',
		array(
			'label_for' => 'ggpush_baidu_token',
			'form_type' => 'input',
			'type'      => 'text',
			'form_desc' => __( 'Please fill in the value of the token field in the API call address', 'ggpush' )
		)
	);

	add_settings_field(
		'ggpush_baidu_interval',
		// 输入框说明文字
		__( 'Push interval', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section',
		array(
			'label_for' => 'ggpush_baidu_interval',
			'form_type' => 'input',
			'type'      => 'number',
			'form_desc' => __( 'How many minutes to push once.If it is set to 0, it will not be pushed', 'ggpush' )
		)
	);

	add_settings_field(
		'ggpush_baidu_num',
		// 输入框说明文字
		__( 'Number of links per push', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section',
		array(
			'label_for' => 'ggpush_baidu_num',
			'form_type' => 'input',
			'type'      => 'number'
		)
	);

	add_settings_field(
		'ggpush_baidu_type',
		// 输入框说明文字
		__( 'Push mode', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section',
		array(
			'label_for' => 'ggpush_baidu_type',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Latest', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'Random', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	add_settings_field(
		'ggpush_baidu_add_push',
		// 输入框说明文字
		__( 'Push after the article is published', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section',
		array(
			'label_for' => 'ggpush_baidu_add_push',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Yes', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'No', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	// 百度搜索引擎快速收录
	add_settings_section(
		'ggpush_section_baidu_fast',
		__( 'Baidu search engine fast collection', 'ggpush' ),
		null,
		'ggpush'
	);

	add_settings_field(
		'ggpush_baidu_fast_interval',
		// 输入框说明文字
		__( 'Push interval', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_baidu_fast',
		array(
			'label_for' => 'ggpush_baidu_fast_interval',
			'form_type' => 'input',
			'type'      => 'number',
			'form_desc' => __( 'How many minutes to push once.If it is set to 0, it will not be pushed', 'ggpush' )
		)
	);

	add_settings_field(
		'ggpush_baidu_fast_num',
		// 输入框说明文字
		__( 'Number of links per push', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_baidu_fast',
		array(
			'label_for' => 'ggpush_baidu_fast_num',
			'form_type' => 'input',
			'type'      => 'number'
		)
	);

	add_settings_field(
		'ggpush_baidu_fast_type',
		// 输入框说明文字
		__( 'Push mode', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_baidu_fast',
		array(
			'label_for' => 'ggpush_baidu_fast_type',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Latest', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'Random', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	add_settings_field(
		'ggpush_baidu_add_fast_push',
		// 输入框说明文字
		__( 'Push after the article is published', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_baidu_fast',
		array(
			'label_for' => 'ggpush_baidu_add_fast_push',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Yes', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'No', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	// 必应搜索引擎
	add_settings_section(
		'ggpush_section_bing',
		__( 'Bing search engine', 'ggpush' ),
		null,
		'ggpush'
	);

	// 在新的设置页面添加表单输入框
	add_settings_field(
		'ggpush_bing_token',
		// 输入框说明文字
		__( 'API key', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_bing',
		array(
			'label_for' => 'ggpush_bing_token',
			'form_type' => 'input',
			'type'      => 'text'
		)
	);

	add_settings_field(
		'ggpush_bing_interval',
		// 输入框说明文字
		__( 'Push interval', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_bing',
		array(
			'label_for' => 'ggpush_bing_interval',
			'form_type' => 'input',
			'type'      => 'number',
			'form_desc' => __( 'How many minutes to push once.If it is set to 0, it will not be pushed', 'ggpush' )
		)
	);

	add_settings_field(
		'ggpush_bing_num',
		// 输入框说明文字
		__( 'Number of links per push', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_bing',
		array(
			'label_for' => 'ggpush_bing_num',
			'form_type' => 'input',
			'type'      => 'number'
		)
	);

	add_settings_field(
		'ggpush_bing_type',
		// 输入框说明文字
		__( 'Push mode', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_bing',
		array(
			'label_for' => 'ggpush_bing_type',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Latest', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'Random', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	add_settings_field(
		'ggpush_bing_add_push',
		// 输入框说明文字
		__( 'Push after the article is published', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_bing',
		array(
			'label_for' => 'ggpush_bing_add_push',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Yes', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'No', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	// IndexNow
	add_settings_section(
		'ggpush_section_indexnow',
		__( 'IndexNow', 'ggpush' ),
		null,
		'ggpush'
	);

	add_settings_field(
		'ggpush_indexnow_token',
		// 输入框说明文字
		__( 'Key', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_indexnow',
		array(
			'label_for' => 'ggpush_indexnow_token',
			'form_type' => 'input',
			'type'      => 'text',
			'form_desc' => __( 'The key is a 32-bit random string. After filling in the key and saving it, a key text file with the same name will be generated in the root directory of your website', 'ggpush' )
		)
	);

	add_settings_field(
		'ggpush_indexnow_interval',
		// 输入框说明文字
		__( 'Push interval', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_indexnow',
		array(
			'label_for' => 'ggpush_indexnow_interval',
			'form_type' => 'input',
			'type'      => 'number',
			'form_desc' => __( 'How many minutes to push once.If it is set to 0, it will not be pushed', 'ggpush' )
		)
	);

	add_settings_field(
		'ggpush_indexnow_num',
		// 输入框说明文字
		__( 'Number of links per push', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_indexnow',
		array(
			'label_for' => 'ggpush_indexnow_num',
			'form_type' => 'input',
			'type'      => 'number'
		)
	);

	add_settings_field(
		'ggpush_indexnow_type',
		// 输入框说明文字
		__( 'Push mode', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_indexnow',
		array(
			'label_for' => 'ggpush_indexnow_type',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Latest', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'Random', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	add_settings_field(
		'ggpush_indexnow_add_push',
		// 输入框说明文字
		__( 'Push after the article is published', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_indexnow',
		array(
			'label_for' => 'ggpush_indexnow_add_push',
			'form_type' => 'select',
			'form_data' => [
				[
					'title' => __( 'Yes', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'No', 'ggpush' ),
					'value' => '2'
				]
			]
		)
	);

	add_settings_field(
		'ggpush_indexnow_search_engine',
		// 输入框说明文字
		__( 'Search engine', 'ggpush' ),
		'ggpush_field_callback',
		'ggpush',
		'ggpush_section_indexnow',
		array(
			'label_for' => 'ggpush_indexnow_search_engine',
			'form_type' => 'checkbox',
			'form_data' => [
				[
					'title' => __( 'IndexNow', 'ggpush' ),
					'value' => '1'
				],
				[
					'title' => __( 'Microsoft Bing', 'ggpush' ),
					'value' => '2'
				],
				[
					'title' => __( 'Yandex', 'ggpush' ),
					'value' => '3'
				]
			]
		)
	);
}

/**
 * 添加菜单到后台
 */
function ggpush_options_page() {
	add_menu_page(
		__( 'Ggpush', 'ggpush' ),
		__( 'Ggpush', 'ggpush' ),
		'manage_options',
		'#ggpush',
		null,
		'dashicons-admin-links'
	);

	add_submenu_page(
		'#ggpush',
		__( 'Push record', 'ggpush' ),
		__( 'Push record', 'ggpush' ),
		'manage_options',
		'ggpush_record',
		'ggpush_record_html'
	);

	add_submenu_page(
		'#ggpush',
		__( 'Settings', 'ggpush' ),
		__( 'Settings', 'ggpush' ),
		'manage_options',
		'ggpush_settings',
		'ggpush_settings_html'
	);

	remove_submenu_page( '#ggpush', '#ggpush' );
}

/**
 * 设置后台菜单回调函数
 */
function ggpush_settings_html() {
	// 检查用户权限
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// 添加错误/更新信息

	// 检查用户是否提交了表单
	// 如果提交了表单，WordPress 会添加 "settings-updated" 参数到 $_GET 里。
	if ( ! empty( $_GET['settings-updated'] ) ) {
		// 添加更新信息
		add_settings_error( 'ggpush_messages', 'ggpush_message', __( 'Settings saved.', 'ggpush' ), 'updated' );
		// 更新定时任务
		ggpush_update_cron();
	}

	// 显示错误/更新信息
	settings_errors( 'ggpush_messages' );

	?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
			<?php
			// 输出表单
			settings_fields( 'ggpush' );
			// 输出表单说明名字
			do_settings_sections( 'ggpush' );
			// 输出保存设置按钮
			submit_button( __( 'Save Changes', 'ggpush' ) );
			?>
        </form>
    </div>
	<?php
}

/**
 * 推送记录
 */
function ggpush_record_html() {
	// 检查用户权限
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table_name  = $wpdb->prefix . 'ggpush_records';
	$current_url = self_admin_url( 'admin.php?page=ggpush_record' );
	// 清除记录
	if ( isset( $_GET['ggpush_clear_day'] ) ) {
		$ggpush_clear_day = (int) $_GET['ggpush_clear_day'];
		if ( $ggpush_clear_day <= 0 ) {
			$end_record_date_time = time();
		} else if ( 1 === $ggpush_clear_day ) {
			$end_record_date_time = strtotime( '-1 day' );
		} else {
			$end_record_date_time = strtotime( '-' . $ggpush_clear_day . ' days' );
		}
		$end_record_date = date( 'Y-m-d H:i:s', $end_record_date_time );
		$sql             = 'DELETE FROM `' . $table_name . '` where `record_date` <= "' . $end_record_date . '"';
		$wpdb->query( $sql );
		require_once GGPUSH_PLUGIN_DIR . 'views/record-clear.php';
		exit();
	}
	require_once GGPUSH_PLUGIN_DIR . 'views/record-index.php';
}

/**
 * 在插件页面添加设置链接
 *
 * @param $links
 *
 * @return mixed
 */
function ggpush_add_settings_link( $links ) {
	$settings_link = '<a href="admin.php?page=ggpush_settings">' . __( 'Settings', 'ggpush' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

/**
 * 更新定时任务
 */
function ggpush_update_cron() {
	if ( is_plugin_active( 'ggpush/ggpush.php' ) ) {
		// 插件已激活，启用定时任务
		ggpush_create_baidu_cron();
		ggpush_create_baidu_fast_cron();
		ggpush_create_bing_cron();
		ggpush_create_indexnow_cron();
	} else {
		// 插件已禁用，删除定时任务
		ggpush_delete_baidu_cron();
		ggpush_delete_baidu_fast_cron();
		ggpush_delete_bing_cron();
		ggpush_delete_indexnow_cron();
	}
}

/**
 * 定时任务过滤器
 *
 * @param $schedules
 *
 * @return mixed
 */
function ggpush_cron_schedules( $schedules ) {
	$options = get_option( 'ggpush_options' );

	// 百度普通收录
	$baidu_cron_interval = intval( $options['ggpush_baidu_interval'] ?? 0 );
	if ( $baidu_cron_interval > 0 ) {
		$schedules['ggpush_baidu_cron'] = array(
			'interval' => $baidu_cron_interval * 60,
			'display'  => __( 'Baidu search engine general collection', 'ggpush' ),
		);
	} else {
		unset( $schedules['ggpush_baidu_cron'] );
	}

	// 百度快速收录
	$baidu_fast_cron_interval = intval( $options['ggpush_baidu_fast_interval'] ?? 0 );
	if ( $baidu_fast_cron_interval > 0 ) {
		$schedules['ggpush_baidu_fast_cron'] = array(
			'interval' => $baidu_fast_cron_interval * 60,
			'display'  => __( 'Baidu search engine fast collection', 'ggpush' ),
		);
	} else {
		unset( $schedules['ggpush_baidu_fast_cron'] );
	}

	// bing收录
	$bing_cron_interval = intval( $options['ggpush_bing_interval'] ?? 0 );
	if ( $bing_cron_interval > 0 ) {
		$schedules['ggpush_bing_cron'] = array(
			'interval' => $bing_cron_interval * 60,
			'display'  => __( 'Bing search engine', 'ggpush' ),
		);
	} else {
		unset( $schedules['ggpush_bing_cron'] );
	}

	// indexnow收录
	$indexnow_cron_interval = intval( $options['ggpush_indexnow_interval'] ?? 0 );
	if ( $indexnow_cron_interval > 0 ) {
		$schedules['ggpush_indexnow_cron'] = array(
			'interval' => $indexnow_cron_interval * 60,
			'display'  => __( 'IndexNow', 'ggpush' ),
		);
	} else {
		unset( $schedules['ggpush_indexnow_cron'] );
	}

	return $schedules;
}

/**
 * 创建百度普通收录定时任务
 */
function ggpush_create_baidu_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_baidu_token'] ) && ! empty( $options['ggpush_baidu_interval'] ) && ! empty( $options['ggpush_baidu_num'] ) ) {
		if ( ! wp_next_scheduled( 'ggpush_run_baidu_cron' ) ) {
			wp_schedule_event( time(), 'ggpush_baidu_cron', 'ggpush_run_baidu_cron' );
		}
	} else {
		ggpush_delete_baidu_cron();
	}
}

/**
 * 删除百度普通收录定时任务
 */
function ggpush_delete_baidu_cron() {
	$timestamp = wp_next_scheduled( 'ggpush_run_baidu_cron' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'ggpush_run_baidu_cron' );
	}
}

/**
 * 创建百度快速收录定时任务
 */
function ggpush_create_baidu_fast_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_baidu_token'] ) && ! empty( $options['ggpush_baidu_fast_interval'] ) && ! empty( $options['ggpush_baidu_fast_num'] ) ) {
		if ( ! wp_next_scheduled( 'ggpush_run_baidu_fast_cron' ) ) {
			wp_schedule_event( time(), 'ggpush_baidu_fast_cron', 'ggpush_run_baidu_fast_cron' );
		}
	} else {
		ggpush_delete_baidu_fast_cron();
	}
}

/**
 * 删除百度快速收录定时任务
 */
function ggpush_delete_baidu_fast_cron() {
	$timestamp = wp_next_scheduled( 'ggpush_run_baidu_fast_cron' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'ggpush_run_baidu_fast_cron' );
	}
}

/**
 * 创建bing收录定时任务
 */
function ggpush_create_bing_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_bing_token'] ) && ! empty( $options['ggpush_bing_interval'] ) && ! empty( $options['ggpush_bing_num'] ) ) {
		if ( ! wp_next_scheduled( 'ggpush_run_bing_cron' ) ) {
			wp_schedule_event( time(), 'ggpush_bing_cron', 'ggpush_run_bing_cron' );
		}
	} else {
		ggpush_delete_bing_cron();
	}
}

/**
 * 删除bing收录定时任务
 */
function ggpush_delete_bing_cron() {
	$timestamp = wp_next_scheduled( 'ggpush_run_bing_cron' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'ggpush_run_bing_cron' );
	}
}

/**
 * 创建indexnow收录定时任务
 */
function ggpush_create_indexnow_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_indexnow_token'] ) && ! empty( $options['ggpush_indexnow_interval'] ) && ! empty( $options['ggpush_indexnow_num'] ) ) {
		$keyLocation = ABSPATH . 'ggpush_' . $options['ggpush_indexnow_token'] . '.txt';
		file_put_contents( $keyLocation, $options['ggpush_indexnow_token'] );
		if ( ! wp_next_scheduled( 'ggpush_run_indexnow_cron' ) ) {
			wp_schedule_event( time(), 'ggpush_indexnow_cron', 'ggpush_run_indexnow_cron' );
		}
	} else {
		ggpush_delete_indexnow_cron();
	}
}

/**
 * 删除indexnow收录定时任务
 */
function ggpush_delete_indexnow_cron() {
	$timestamp = wp_next_scheduled( 'ggpush_run_indexnow_cron' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'ggpush_run_indexnow_cron' );
	}
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_indexnow_token'] ) ) {
		$keyLocation = ABSPATH . 'ggpush_' . $options['ggpush_indexnow_token'] . '.txt';
		wp_delete_file( $keyLocation );
	}
}

/**
 * 获取推送文章网址
 *
 * @param int $num 推送数据
 * @param bool $random 是否随机
 *
 * @return array
 */
function ggpush_get_post_url( int $num, bool $random ): array {
	$data = [];
	if ( $num > 0 ) {
		$args      = array(
			'orderby'        => $random ? 'rand' : 'ID',
			'order'          => 'DESC',
			'post_status'    => 'publish',
			'posts_per_page' => $num
		);
		$the_query = new WP_Query( $args );
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$data[] = get_permalink();
		}
	}

	return $data;
}

/**
 * 百度推送
 *
 * @param array $urls
 * @param bool $daily
 */
function ggpush_push_baidu( array $urls, $daily = false ) {
	$options             = get_option( 'ggpush_options' );
	$push                = new Ggpush();
	$response            = $push->push( $urls, parse_url( get_home_url(), PHP_URL_HOST ), $options['ggpush_baidu_token'], $daily ? 'daily' : '' );
	$record_result       = wp_remote_retrieve_body( $response );
	$record_result_error = '';
	if ( ! empty( $record_result ) ) {
		$tmp = json_decode( $record_result, true );
		if ( ! empty( $tmp['success'] ) && $tmp['success'] > 0 ) {
			$record_result_status = 1;
		} else {
			$record_result_status = 2;
			if ( ! empty( $tmp['message'] ) ) {
				$record_result_error = $tmp['message'];
			} else {
				if ( ! empty( $tmp['not_same_site'] ) ) {
					$record_result_error = '推送网址错误';
				}
			}
		}
	} else {
		$record_result_status = 2;
	}
	$data = [
		'record_platform'      => '1',
		'record_mode'          => '1',
		'record_urls'          => json_encode( $urls ),
		'record_num'           => count( $urls ),
		'record_result'        => $record_result,
		'record_result_code'   => (int) wp_remote_retrieve_response_code( $response ),
		'record_result_status' => $record_result_status,
		'record_result_error'  => $record_result_error,
	];
	ggpush_save_record( $data );
}

/**
 * 百度普通推送
 */
function ggpush_run_baidu_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_baidu_token'] ) && ! empty( $options['ggpush_baidu_interval'] ) && ! empty( $options['ggpush_baidu_num'] ) ) {
		$urls = ggpush_get_post_url( $options['ggpush_baidu_num'], $options['ggpush_baidu_type'] == 2 );
		if ( ! empty( $urls ) ) {
			ggpush_push_baidu( $urls );
		}
	}
}

/**
 * 百度快速推送
 */
function ggpush_run_baidu_fast_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_baidu_token'] ) && ! empty( $options['ggpush_baidu_fast_interval'] ) && ! empty( $options['ggpush_baidu_fast_num'] ) ) {
		$urls = ggpush_get_post_url( $options['ggpush_baidu_fast_num'], $options['ggpush_baidu_fast_type'] == 2 );
		if ( ! empty( $urls ) ) {
			ggpush_push_baidu( $urls, true );
		}
	}
}

/**
 * 推送链接到bing
 *
 * @param $urls
 */
function ggpush_push_bing( $urls ) {
	$options             = get_option( 'ggpush_options' );
	$push                = new Ggpush();
	$response            = $push->bingPush( $urls, get_home_url(), $options['ggpush_bing_token'] );
	$record_result       = wp_remote_retrieve_body( $response );
	$record_result_error = '';
	if ( ! empty( $record_result ) ) {
		$tmp = json_decode( $record_result, true );
		if ( isset( $tmp['d'] ) && $tmp['d'] === null ) {
			$record_result_status = 1;
		} else {
			$record_result_status = 2;
			if ( ! empty( $tmp['Message'] ) ) {
				$record_result_error = $tmp['Message'];
			}
		}
	} else {
		$record_result_status = 2;
	}
	$data = [
		'record_platform'      => '6',
		'record_mode'          => '4',
		'record_urls'          => json_encode( $urls ),
		'record_num'           => count( $urls ),
		'record_result'        => $record_result,
		'record_result_code'   => (int) wp_remote_retrieve_response_code( $response ),
		'record_result_status' => $record_result_status,
		'record_result_error'  => $record_result_error,
	];
	ggpush_save_record( $data );
}

/**
 * bing推送
 */
function ggpush_run_bing_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_bing_token'] ) && ! empty( $options['ggpush_bing_interval'] ) && ! empty( $options['ggpush_bing_num'] ) ) {
		$urls = ggpush_get_post_url( $options['ggpush_bing_num'], $options['ggpush_bing_type'] == 2 );
		if ( ! empty( $urls ) ) {
			ggpush_push_bing( $urls );
		}
	}
}

/**
 * 推送链接到indexnow
 *
 * @param $urls
 */
function ggpush_push_indexnow( $urls ) {
	$options     = get_option( 'ggpush_options' );
	$push        = new Ggpush();
	$keyLocation = get_home_url() . 'ggpush_' . $options['ggpush_indexnow_token'] . '.txt';
	foreach ( $options['ggpush_indexnow_search_engine'] as $v ) {
		$host            = 'api.indexnow.org';
		$record_platform = 8;
		switch ( $v ) {
			case 1:
				$record_platform = 8;
				$host            = 'api.indexnow.org';
				break;
			case 2:
				$record_platform = 6;
				$host            = 'www.bing.com';
				break;
			case 3:
				$record_platform = 9;
				$host            = 'yandex.com';
				break;
		}
		$response            = $push->indexNowPush( $urls, parse_url( get_home_url(), PHP_URL_HOST ), $options['ggpush_indexnow_token'], $keyLocation, $host );
		$record_result       = wp_remote_retrieve_body( $response );
		$record_result_error = '';
		if ( ! empty( $record_result ) ) {
			$record_result = trim( strip_tags( $record_result ) );
		}
		$record_result_code = (int) wp_remote_retrieve_response_code( $response );
		if ( $record_result_code === 200 ) {
			$record_result_status = 1;
		} else {
			$record_result_status = 2;
		}
		$data = [
			'record_platform'      => $record_platform,
			'record_mode'          => '5',
			'record_urls'          => json_encode( $urls ),
			'record_num'           => count( $urls ),
			'record_result'        => $record_result,
			'record_result_code'   => $record_result_code,
			'record_result_status' => $record_result_status,
			'record_result_error'  => $record_result_error,
		];
		ggpush_save_record( $data );
	}
}

/**
 * indexnow推送
 */
function ggpush_run_indexnow_cron() {
	$options = get_option( 'ggpush_options' );
	if ( ! empty( $options['ggpush_indexnow_token'] ) && ! empty( $options['ggpush_indexnow_interval'] ) && ! empty( $options['ggpush_indexnow_num'] ) ) {
		$urls = ggpush_get_post_url( $options['ggpush_indexnow_num'], $options['ggpush_indexnow_type'] == 2 );
		if ( ! empty( $urls ) && ! empty( $options['ggpush_indexnow_search_engine'] ) ) {
			ggpush_push_indexnow( $urls );
		}
	}
}

/**
 * 保存推送日志记录
 *
 * @param $data
 *
 * @return bool|int|mysqli_result|resource|null
 */
function ggpush_save_record( $data ) {
	if ( empty( $data ) ) {
		return false;
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'ggpush_records';

	return $wpdb->insert( $table_name, $data );
}

/**
 * 格式化推送平台
 *
 * @param $record_platform
 *
 * @return mixed|string|void
 */
function ggpush_format_record_platform( $record_platform ) {
	switch ( $record_platform ) {
		case 1:
			return __( 'Baidu', 'ggpush' );
		case 2:
			return __( '360', 'ggpush' );
		case 3:
			return __( 'Sogou', 'ggpush' );
		case 4:
			return __( 'Toutiao', 'ggpush' );
		case 5:
			return __( 'Sm', 'ggpush' );
		case 6:
			return __( 'Bing', 'ggpush' );
		case 7:
			return __( 'Google', 'ggpush' );
		case 8:
			return __( 'Indexnow', 'ggpush' );
		case 9:
			return __( 'Yandex', 'ggpush' );
	}

	return $record_platform;
}

/**
 * 格式化推送方式
 *
 * @param $record_mode
 *
 * @return mixed|string|void
 */
function ggpush_format_record_mode( $record_mode ) {
	switch ( $record_mode ) {
		case 1:
			return __( 'General collection', 'ggpush' );
		case 2:
			return __( 'Fast collection', 'ggpush' );
		case 3:
			return __( 'Js submit', 'ggpush' );
		case 4:
			return __( 'Api submit', 'ggpush' );
		case 5:
			return __( 'Indexnow', 'ggpush' );
	}

	return $record_mode;
}

/**
 * 格式化推送结果
 *
 * @param $result_status
 *
 * @return mixed|string|void
 */
function ggpush_format_result_status( $result_status ) {
	switch ( $result_status ) {
		case 1:
			return __( 'Success', 'ggpush' );
		case 2:
			return __( 'Failed', 'ggpush' );
		case 3:
			return __( 'Unknown', 'ggpush' );
	}

	return $result_status;
}