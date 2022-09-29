<?php

/**
 * 定时推送
 */
class Ggpush_Task
{

    /**
     * 初始化页面
     * @return void
     */
    public static function init_page()
    {
        self::add_baidu_page();
        self::add_bing_page();
        self::add_indexnow_page();
    }

    /**
     * 显示定时推送界面
     * @return void
     */
    public static function show_page()
    {
        $tab = Ggpush_Plugin::get('tab', 'ggpush-timing-push');
        if (!empty($_GET['settings-updated'])) {
            // 添加更新信息
            add_settings_error('ggpush_messages', 'ggpush_message', '设置已保存。', 'updated');
        }
        // 显示错误/更新信息
        settings_errors('ggpush_messages');
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
            <nav class="nav-tab-wrapper wp-clearfix">
                <a href="admin.php?page=ggpush-timing-push"
                   class="nav-tab<?php if (empty($tab) || 'ggpush-timing-push' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">推送状态</a>
                <a href="admin.php?page=ggpush-timing-push&tab=ggpush-baidu-page"
                   class="nav-tab<?php if ('ggpush-baidu-page' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">百度推送</a>
                <a href="admin.php?page=ggpush-timing-push&tab=ggpush-bing-page"
                   class="nav-tab<?php if ('ggpush-bing-page' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">必应推送</a>
                <a href="admin.php?page=ggpush-timing-push&tab=ggpush-indexnow-page"
                   class="nav-tab<?php if ('ggpush-indexnow-page' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">IndexNow推送</a>
            </nav>
            <?php if ((empty($tab) || 'ggpush-timing-push' === $tab)) { ?>
                <form id="ggpush-task-table-form" method="get">
                    <input type="hidden" name="page" value="ggpush-timing-push"/>
                    <?php
                    $ggpush_task_table = new Ggpush_Task_Table();
                    $ggpush_task_table->prepare_items();
                    $ggpush_task_table->display();
                    ?>
                </form>
            <?php } else { ?>
                <form action="options.php" method="post">
                    <input type="hidden" name="page" value="ggpush-timing-push">
                    <?php
                    // 输出表单
                    settings_fields($tab);
                    do_settings_sections($tab);
                    // 输出保存设置按钮
                    submit_button('保存更改');
                    ?>
                </form>
            <?php } ?>
        </div>
        <?php
    }

    // 百度
    public static function add_baidu_page()
    {
        // 注册一个新页面
        register_setting('ggpush-baidu-page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        add_settings_section(
            'ggpush_section',
            '普通收录',
            array('Ggpush_Task', 'baidu_callback'),
            'ggpush-baidu-page'
        );

        add_settings_field(
            'baidu_interval',
            '推送间隔',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-baidu-page',
            'ggpush_section',
            array(
                'label_for' => 'baidu_interval',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '多少分钟推送一次，设置为0则不推送'
            )
        );

        add_settings_field(
            'baidu_num',
            '每次推送链接数量',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-baidu-page',
            'ggpush_section',
            array(
                'label_for' => 'baidu_num',
                'form_type' => 'input',
                'type' => 'number'
            )
        );

        add_settings_field(
            'baidu_type',
            '推送文章',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-baidu-page',
            'ggpush_section',
            array(
                'label_for' => 'baidu_type',
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

        // 百度搜索引擎快速收录
        add_settings_section(
            'ggpush_section_baidu_fast',
            '快速收录',
            array('Ggpush_Task', 'baidu_fast_callback'),
            'ggpush-baidu-page'
        );

        add_settings_field(
            'baidu_fast_interval',
            '推送间隔',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-baidu-page',
            'ggpush_section_baidu_fast',
            array(
                'label_for' => 'baidu_fast_interval',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '多少分钟推送一次，设置为0则不推送'
            )
        );

        add_settings_field(
            'baidu_fast_num',
            '每次推送链接数量',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-baidu-page',
            'ggpush_section_baidu_fast',
            array(
                'label_for' => 'baidu_fast_num',
                'form_type' => 'input',
                'type' => 'number'
            )
        );

        add_settings_field(
            'baidu_fast_type',
            '推送文章',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-baidu-page',
            'ggpush_section_baidu_fast',
            array(
                'label_for' => 'baidu_fast_type',
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
    }

    /**
     * 百度说明
     * @return void
     */
    public static function baidu_callback()
    {
        ?>
        <p>
            <a href="https://ziyuan.baidu.com/linksubmit/index" target="_blank">普通收录</a>工具可以向百度搜索主动推送资源，缩短爬虫发现网站链接的时间，不保证收录和展现效果。
        </p>
        <?php
    }

    /**
     * 百度说明
     * @return void
     */
    public static function baidu_fast_callback()
    {
        ?>
        <p>
            <a href="https://ziyuan.baidu.com/dailysubmit/index" target="_blank">快速收录</a>工具可以向百度搜索主动推送资源，缩短爬虫发现网站链接的时间，对于高实效性内容推荐使用快速收录工具，实时向搜索推送资源。
        </p>
        <?php
    }

    /**
     * 必应
     * @return void
     */
    public static function add_bing_page()
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

        add_settings_field(
            'bing_interval',
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
            '推送文章',
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
    }

    /**
     * IndexNow
     * @return void
     */
    public static function add_indexnow_page()
    {
        // 注册一个新页面
        register_setting('ggpush-indexnow-page', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        // IndexNow
        add_settings_section(
            'ggpush_section_indexnow',
            null,
            null,
            'ggpush-indexnow-page'
        );

        add_settings_field(
            'indexnow_interval',
            '推送间隔',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-indexnow-page',
            'ggpush_section_indexnow',
            array(
                'label_for' => 'indexnow_interval',
                'form_type' => 'input',
                'type' => 'number',
                'form_desc' => '多少分钟推送一次，设置为0则不推送'
            )
        );

        add_settings_field(
            'indexnow_num',
            '每次推送链接数量',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-indexnow-page',
            'ggpush_section_indexnow',
            array(
                'label_for' => 'indexnow_num',
                'form_type' => 'input',
                'type' => 'number'
            )
        );

        add_settings_field(
            'indexnow_type',
            '推送文章',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-indexnow-page',
            'ggpush_section_indexnow',
            array(
                'label_for' => 'indexnow_type',
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
    }
}