<?php

/**
 * bing页面
 */
class Ggpush_Bing_Page
{

    // 初始化页面
    public static function init_page()
    {
        // 注册一个新页面
        register_setting('ggpush-bing-page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        // 必应搜索引擎
        add_settings_section(
            'ggpush_section_bing',
            null,
            null,
            'ggpush-bing-page'
        );

        // 在新的设置页面添加表单输入框
        add_settings_field(
            'bing_token',
            // 输入框说明文字
            'API密钥',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-bing-page',
            'ggpush_section_bing',
            array(
                'label_for' => 'bing_token',
                'form_type' => 'input',
                'type' => 'text'
            )
        );

        add_settings_field(
            'bing_interval',
            // 输入框说明文字
            '推送间隔',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-bing-page',
            'ggpush_section_bing',
            array(
                'label_for' => 'bing_interval',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '多少分钟推送一次，设置为0则不推送'
            )
        );

        add_settings_field(
            'bing_num',
            // 输入框说明文字
            '每次推送链接数量',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-bing-page',
            'ggpush_section_bing',
            array(
                'label_for' => 'bing_num',
                'form_type' => 'input',
                'type' => 'number'
            )
        );

        add_settings_field(
            'bing_type',
            // 输入框说明文字
            '推送方式',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-bing-page',
            'ggpush_section_bing',
            array(
                'label_for' => 'bing_type',
                'form_type' => 'select',
                'form_data' => array(
                    array(
                        'title' => '最新',
                        'value' => '1'
                    ),
                    array(
                        'title' => '随机',
                        'value' => '2'
                    ),
                    array(
                        'title' => '伪随机',
                        'value' => '3'
                    )
                )
            )
        );

        add_settings_field(
            'bing_add_push',
            // 输入框说明文字
            '发布文章后推送',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-bing-page',
            'ggpush_section_bing',
            array(
                'label_for' => 'bing_add_push',
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
    }
}