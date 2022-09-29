<?php

/**
 * 推送设置
 */
class Ggpush_Settings
{
    /**
     * 初始化页面
     * @return void
     */
    public static function init_page()
    {
        self::add_settings_page();
        self::add_baidu_page();
        self::add_bing_page();
        self::add_indexnow_page();
    }

    /**
     * 显示设置页面
     * @return void
     */
    public static function show_page()
    {
        // 检查用户权限
        if (!current_user_can('manage_options')) {
            return;
        }
        $tab = Ggpush_Plugin::get('tab', 'ggpush-settings');
        if (!empty($_GET['settings-updated'])) {
            // 添加更新信息
            add_settings_error('ggpush_messages', 'ggpush_message', '设置已保存。', 'updated');
            // 更新定时任务
            Ggpush_Cron::update_cron();
        }
        // 显示错误/更新信息
        settings_errors('ggpush_messages');
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
            <nav class="nav-tab-wrapper wp-clearfix">
                <a href="admin.php?page=ggpush-settings"
                   class="nav-tab<?php if (empty($tab) || 'ggpush-settings' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">基本设置</a>
                <a href="admin.php?page=ggpush-settings&tab=ggpush-baidu-page"
                   class="nav-tab<?php if ('ggpush-baidu-page' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">百度推送</a>
                <a href="admin.php?page=ggpush-settings&tab=ggpush-bing-page"
                   class="nav-tab<?php if ('ggpush-bing-page' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">必应推送</a>
                <a href="admin.php?page=ggpush-settings&tab=ggpush-indexnow-page"
                   class="nav-tab<?php if ('ggpush-indexnow-page' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">IndexNow推送</a>
            </nav>
            <form action="options.php" method="post">
                <input type="hidden" name="page" value="ggpush-settings">
                <?php
                // 输出表单
                settings_fields($tab);
                do_settings_sections($tab);
                // 输出保存设置按钮
                submit_button('保存更改');
                ?>
            </form>
        </div>
        <?php
    }

    // 基本设置页面
    public static function add_settings_page()
    {
        // 注册一个新页面
        register_setting('ggpush-settings', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        add_settings_section(
            'ggpush_section_base',
            null,
            null,
            'ggpush-settings'
        );


        add_settings_field(
            'menu_position',
            '显示位置',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-settings',
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
            '请求超时时间',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-settings',
            'ggpush_section_base',
            array(
                'label_for' => 'push_timeout',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '请求超过多少秒后，自动停止推送，默认为30秒'
            )
        );

        add_settings_field(
            'push_record',
            '推送记录',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-settings',
            'ggpush_section_base',
            array(
                'label_for' => 'push_record',
                'form_type' => 'select',
                'form_data' => array(
                    array(
                        'title' => '开启',
                        'value' => '0'
                    ),
                    array(
                        'title' => '关闭',
                        'value' => '1'
                    )
                ),
                'form_desc' => '保存推送请求与响应数据，可以在推送记录页面查看推送记录'
            )
        );
    }

    // 百度
    public static function add_baidu_page()
    {
        // 注册一个新页面
        register_setting('ggpush-baidu-page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        add_settings_section(
            'ggpush_section',
            '说明',
            array('Ggpush_Settings', 'baidu_callback'),
            'ggpush-baidu-page'
        );

        add_settings_field(
            'baidu_token',
            '准入密钥',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-baidu-page',
            'ggpush_section',
            array(
                'label_for' => 'baidu_token',
                'form_type' => 'input',
                'type' => 'text',
                'form_desc' => '请填写接口调用地址中的token参数值'
            )
        );
    }

    /**
     * 百度说明
     * @return void
     */
    public static function baidu_callback()
    {
        ?>
        <p>
            准入密钥可以在<a href="https://ziyuan.baidu.com/linksubmit/index" target="_blank" style="margin: 0 5px;">https://ziyuan.baidu.com/linksubmit/index</a>页面获取（接口调用地址中的<b>token</b>参数值）。
        </p>
        <?php
    }

    // 必应
    public static function add_bing_page()
    {
        // 注册一个新页面
        register_setting('ggpush-bing-page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        // 必应搜索引擎
        add_settings_section(
            'ggpush_section_bing',
            '说明',
            array('Ggpush_Settings', 'bing_callback'),
            'ggpush-bing-page'
        );

        add_settings_field(
            'bing_token',
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
    }

    /**
     * 必应说明
     * @return void
     */
    public static function bing_callback()
    {
        ?>
        <p>
            API密钥可以在<a href="https://www.bing.com/webmasters/home" target="_blank"
                       style="margin: 0 5px;">https://www.bing.com/webmasters/home</a>页面获取（依次点击：页面右上方的设置按钮、API 访问、API
            密钥）。
        </p>
        <?php
    }

    // indexnow
    public static function add_indexnow_page()
    {
        // 注册一个新页面
        register_setting('ggpush-indexnow-page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        // IndexNow
        add_settings_section(
            'ggpush_section_indexnow',
            '说明',
            array('Ggpush_Settings', 'indexnow_callback'),
            'ggpush-indexnow-page'
        );

        add_settings_field(
            'indexnow_token',
            '密钥',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-indexnow-page',
            'ggpush_section_indexnow',
            array(
                'label_for' => 'indexnow_token',
                'form_type' => 'input',
                'type' => 'text',
                'form_desc' => '填写密钥保存后，将会在您的网站根目录下生成密钥文本文件'
            )
        );

        add_settings_field(
            'indexnow_search_engine',
            // 输入框说明文字
            '推送平台',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-indexnow-page',
            'ggpush_section_indexnow',
            array(
                'label_for' => 'indexnow_search_engine',
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

    /**
     * indexnow说明
     * @return void
     */
    public static function indexnow_callback()
    {
        ?>
        <p>
            <a href="https://www.indexnow.org/zh_cn/documentation" target="_blank">IndexNow</a>密钥最少有8个，最多128个字符组成。密钥只能包含以下字符：小写字母（a-z），大写字母（A-Z），数字（0-9），以及短破折号（-）。
        </p>
        <p>
            您可以点击右侧链接生成一个随机密钥：<a href="https://www.bing.com/indexnow#generateApiKey" target="_blank">https://www.bing.com/indexnow#generateApiKey</a>。
        </p>
        <?php
    }
}