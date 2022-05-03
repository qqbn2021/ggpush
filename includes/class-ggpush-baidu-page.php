<?php

/**
 * 百度设置页面
 */
class Ggpush_Baidu_Page
{

    // 初始化页面
    public static function init_page()
    {
        // 注册一个新页面
        register_setting('ggpush_baidu_page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        // 百度搜索引擎普通收录
        add_settings_section(
            'ggpush_section',
            '普通收录',
            array('Ggpush_Baidu_Page','baidu_callback'),
            'ggpush_baidu_page'
        );

        // 在新的设置页面添加表单输入框
        add_settings_field(
            'ggpush_baidu_token',
            // 输入框说明文字
            '准入密钥',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section',
            array(
                'label_for' => 'ggpush_baidu_token',
                'form_type' => 'input',
                'type' => 'text',
                'form_desc' => '请填写接口调用地址中的token字段值'
            )
        );

        add_settings_field(
            'ggpush_baidu_interval',
            // 输入框说明文字
            '推送间隔',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section',
            array(
                'label_for' => 'ggpush_baidu_interval',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '多少分钟推送一次，设置为0则不推送'
            )
        );

        add_settings_field(
            'ggpush_baidu_num',
            // 输入框说明文字
            '每次推送链接数量',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section',
            array(
                'label_for' => 'ggpush_baidu_num',
                'form_type' => 'input',
                'type' => 'number'
            )
        );

        add_settings_field(
            'ggpush_baidu_type',
            // 输入框说明文字
            '推送方式',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section',
            array(
                'label_for' => 'ggpush_baidu_type',
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
            'ggpush_baidu_add_push',
            // 输入框说明文字
            '发布文章后推送',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section',
            array(
                'label_for' => 'ggpush_baidu_add_push',
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

        // 百度搜索引擎快速收录
        add_settings_section(
            'ggpush_section_baidu_fast',
            '快速收录',
            array('Ggpush_Baidu_Page','baidu_fast_callback'),
            'ggpush_baidu_page'
        );

        add_settings_field(
            'ggpush_baidu_fast_interval',
            // 输入框说明文字
            '推送间隔',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section_baidu_fast',
            array(
                'label_for' => 'ggpush_baidu_fast_interval',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '多少分钟推送一次，设置为0则不推送'
            )
        );

        add_settings_field(
            'ggpush_baidu_fast_num',
            // 输入框说明文字
            '每次推送链接数量',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section_baidu_fast',
            array(
                'label_for' => 'ggpush_baidu_fast_num',
                'form_type' => 'input',
                'type' => 'number'
            )
        );

        add_settings_field(
            'ggpush_baidu_fast_type',
            // 输入框说明文字
            '推送方式',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section_baidu_fast',
            array(
                'label_for' => 'ggpush_baidu_fast_type',
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
            'ggpush_baidu_add_fast_push',
            // 输入框说明文字
            '发布文章后推送',
            array('Ggpush_Plugin', 'ggpush_field_callback'),
            'ggpush_baidu_page',
            'ggpush_section_baidu_fast',
            array(
                'label_for' => 'ggpush_baidu_add_fast_push',
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

    /**
     * 普通收录回调函数
     * @return void
     */
    public static function baidu_callback()
    {
        ?>
        普通收录工具可以向百度搜索主动推送资源，缩短爬虫发现网站链接的时间，不保证收录和展现效果。
        <?php
    }

    /**
     * 快速收录回调函数
     * @return void
     */
    public static function baidu_fast_callback()
    {
        ?>
        快速收录工具可以向百度搜索主动推送资源，缩短爬虫发现网站链接的时间，对于高实效性内容推荐使用快速收录工具，实时向搜索推送资源。
        <?php
    }
}