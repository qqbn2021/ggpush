<?php

/**
 * 基本设置页面
 */
class Ggpush_Base_Page
{

    // 初始化页面
    public static function init_page()
    {
        // 注册一个新页面
        register_setting('ggpush-base-page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        add_settings_section(
            'ggpush_section_base',
            null,
            null,
            'ggpush-base-page'
        );

        // 在新的设置页面添加表单输入框
        add_settings_field(
            'menu_position',
            // 输入框说明文字
            '菜单显示位置',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-base-page',
            'ggpush_section_base',
            array(
                'label_for' => 'menu_position',
                'form_type' => 'select',
                'form_data' => array(
                    array(
                        'title' => '文章',
                        'value' => '5'
                    ),
                    array(
                        'title' => '媒体',
                        'value' => '10'
                    ),
                    array(
                        'title' => '页面',
                        'value' => '20'
                    ),
                    array(
                        'title' => '评论',
                        'value' => '25'
                    ),
                    array(
                        'title' => '插件',
                        'value' => '65'
                    ),
                    array(
                        'title' => '用户',
                        'value' => '70'
                    ),
                    array(
                        'title' => '工具',
                        'value' => '75'
                    ),
                    array(
                        'title' => '设置',
                        'value' => '80'
                    ),
                    array(
                        'title' => '默认',
                        'value' => '100'
                    )
                )
            )
        );

        add_settings_field(
            'push_timeout',
            // 输入框说明文字
            '请求超时时间',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-base-page',
            'ggpush_section_base',
            array(
                'label_for' => 'push_timeout',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '请求超过多少秒后，自动停止推送，默认为30秒'
            )
        );
    }
}