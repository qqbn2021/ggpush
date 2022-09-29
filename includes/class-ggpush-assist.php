<?php

/**
 * 辅助推送
 */
class Ggpush_Assist
{

    /**
     * 初始化页面
     * @return void
     */
    public static function init_page()
    {
        self::add_article_page();
    }

    /**
     * 显示定时推送界面
     * @return void
     */
    public static function show_page()
    {
        $tab = Ggpush_Plugin::get('tab', 'ggpush-assist-push');
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
                <a href="admin.php?page=ggpush-assist-push"
                   class="nav-tab<?php if (empty($tab) || 'ggpush-assist-push' === $tab) {
                       echo ' nav-tab-active';
                   } ?>">发布文章后推送</a>
            </nav>
            <form action="options.php" method="post">
                <input type="hidden" name="page" value="ggpush-assist-push">
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

    /**
     * 发布文章后推送
     * @return void
     */
    public static function add_article_page()
    {
        // 注册一个新页面
        register_setting('ggpush-assist-push', 'ggpush_options', array('Ggpush_Plugin', 'sanitize'));

        add_settings_section(
            'ggpush_section_article',
            '说明',
            array('Ggpush_Assist', 'article_callback'),
            'ggpush-assist-push'
        );

        add_settings_field(
            'publish_article_platform',
            '推送平台',
            array('Ggpush_Plugin', 'field_callback'),
            'ggpush-assist-push',
            'ggpush_section_article',
            array(
                'label_for' => 'publish_article_platform',
                'form_type' => 'checkbox',
                'form_data' => array(
                    array(
                        'title' => '百度搜索引擎普通收录',
                        'value' => '1'
                    ),
                    array(
                        'title' => '百度搜索引擎快速收录',
                        'value' => '2'
                    ),
                    array(
                        'title' => '必应搜索引擎',
                        'value' => '4'
                    ),
                    array(
                        'title' => 'IndexNow',
                        'value' => '7'
                    )
                )
            )
        );
    }

    /**
     * 发布文章后推送说明
     * @return void
     */
    public static function article_callback()
    {
        ?>
        <p>采用JavaScript异步请求推送方式，勾选多个推送平台不会造成页面卡顿。</p>
        <?php
    }
}