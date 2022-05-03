<?php

/**
 * indexnow页面
 */
class Ggpush_Indexnow_Page {

	// 初始化页面
	public static function init_page() {
		// 注册一个新页面
		register_setting( 'ggpush_indexnow_page', 'ggpush_options' , array('Ggpush_Plugin', 'sanitize'));

		// IndexNow
		add_settings_section(
			'ggpush_section_indexnow',
			null,
			null,
			'ggpush_indexnow_page'
		);

		add_settings_field(
			'ggpush_indexnow_token',
			// 输入框说明文字
			'密钥',
			array( 'Ggpush_Plugin', 'ggpush_field_callback' ),
			'ggpush_indexnow_page',
			'ggpush_section_indexnow',
			array(
				'label_for' => 'ggpush_indexnow_token',
				'form_type' => 'input',
				'type'      => 'text',
				'form_desc' => '密钥为32位随机字符串。填写密钥保存后，将会在您的网站根目录下生成密钥文本文件'
			)
		);

		add_settings_field(
			'ggpush_indexnow_interval',
			// 输入框说明文字
			'推送间隔',
			array( 'Ggpush_Plugin', 'ggpush_field_callback' ),
			'ggpush_indexnow_page',
			'ggpush_section_indexnow',
			array(
				'label_for' => 'ggpush_indexnow_interval',
				'form_type' => 'input',
				'type'      => 'number',
				'form_desc' => '多少分钟推送一次，设置为0则不推送'
			)
		);

		add_settings_field(
			'ggpush_indexnow_num',
			// 输入框说明文字
			'每次推送链接数量',
			array( 'Ggpush_Plugin', 'ggpush_field_callback' ),
			'ggpush_indexnow_page',
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
			'推送方式',
			array( 'Ggpush_Plugin', 'ggpush_field_callback' ),
			'ggpush_indexnow_page',
			'ggpush_section_indexnow',
			array(
				'label_for' => 'ggpush_indexnow_type',
				'form_type' => 'select',
				'form_data' => array(
					array(
						'title' => '最新',
						'value' => '1'
					),
					array(
						'title' => '随机',
						'value' => '2'
					)
				)
			)
		);

		add_settings_field(
			'ggpush_indexnow_add_push',
			// 输入框说明文字
			'发布文章后推送',
			array( 'Ggpush_Plugin', 'ggpush_field_callback' ),
			'ggpush_indexnow_page',
			'ggpush_section_indexnow',
			array(
				'label_for' => 'ggpush_indexnow_add_push',
				'form_type' => 'select',
				'form_data' => array(
					array(
						'title' => '是',
						'value' => '1'
					),
					array(
						'title' => '否',
						'value' => '2'
					)
				)
			)
		);

		add_settings_field(
			'ggpush_indexnow_search_engine',
			// 输入框说明文字
			'搜索引擎',
			array( 'Ggpush_Plugin', 'ggpush_field_callback' ),
			'ggpush_indexnow_page',
			'ggpush_section_indexnow',
			array(
				'label_for' => 'ggpush_indexnow_search_engine',
				'form_type' => 'checkbox',
				'form_data' => array(
					array(
						'title' => 'IndexNow',
						'value' => '1'
					),
					array(
						'title' => '必应',
						'value' => '2'
					),
					array(
						'title' => 'Yandex',
						'value' => '3'
					),
                    array(
                        'title' => 'Seznam.cz',
                        'value' => '4'
                    )
				)
			)
		);
	}
}