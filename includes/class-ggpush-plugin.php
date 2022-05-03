<?php

/**
 * 插件启用、删除、禁用等
 */
class Ggpush_Plugin
{

    // 启用插件
    public static function plugin_activation()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = <<<SQL
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
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//	dbDelta( $sql );
        // 如果表不存在才会执行创建
        maybe_create_table($table_name, $sql);
        // 更新定时任务
        Ggpush_Common::ggpush_update_cron();
    }

    // 删除插件执行的代码
    public static function plugin_uninstall()
    {
        // 删除表
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';
        $wpdb->query('DROP TABLE IF EXISTS `' . $table_name . '`');
        // 删除其它IndexNow密钥文件
        Ggpush_Common::ggpush_delete_indexnow_keyfile(true);
        // 删除配置
        delete_option('ggpush_options');
    }

    // 禁用插件执行的代码
    public static function plugin_deactivation()
    {
        // 插件已禁用，删除定时任务
        Ggpush_Common::ggpush_update_cron(false);
    }

    // 初始化
    public static function admin_init()
    {
        // 注册设置页面
        Ggpush_Base_Page::init_page();
        Ggpush_Baidu_Page::init_page();
        Ggpush_Bing_Page::init_page();
        Ggpush_Indexnow_Page::init_page();
    }

    // 添加菜单
    public static function admin_menu()
    {
        $position = null;
        $options = get_option('ggpush_options');
        if (!empty($options['ggpush_menu_position'])) {
            $position = (int)$options['ggpush_menu_position'];
        }
        // 父菜单
        add_menu_page(
            '果果推送',
            '果果推送',
            'manage_options',
            '#ggpush',
            null,
            'dashicons-admin-links',
            $position
        );
        // 推送记录页面
        add_submenu_page(
            '#ggpush',
            '推送记录',
            '推送记录',
            'manage_options',
            'ggpush_record',
            array('Ggpush_Record_Page', 'home')
        );

        // 计划任务页面
        add_submenu_page(
            '#ggpush',
            '定时任务',
            '定时任务',
            'manage_options',
            'ggpush_task_page',
            array('Ggpush_Task_Page', 'task_list')
        );

        // 基本设置页面
        add_submenu_page(
            '#ggpush',
            '基本设置',
            '基本设置',
            'manage_options',
            'ggpush_base_page',
            array('Ggpush_Plugin', 'show_page')
        );

        // 百度设置页面
        add_submenu_page(
            '#ggpush',
            '百度推送设置',
            '百度推送设置',
            'manage_options',
            'ggpush_baidu_page',
            array('Ggpush_Plugin', 'show_page')
        );

        // bing设置页面
        add_submenu_page(
            '#ggpush',
            '必应推送设置',
            '必应推送设置',
            'manage_options',
            'ggpush_bing_page',
            array('Ggpush_Plugin', 'show_page')
        );

        // indexnow设置页面
        add_submenu_page(
            '#ggpush',
            'IndexNow推送设置',
            'IndexNow推送设置',
            'manage_options',
            'ggpush_indexnow_page',
            array('Ggpush_Plugin', 'show_page')
        );

        remove_submenu_page('#ggpush', '#ggpush');
    }


    // 显示设置页面
    public static function show_page()
    {
        // 检查用户权限
        if (!current_user_can('manage_options')) {
            return;
        }
        // 添加错误/更新信息
        // 检查用户是否提交了表单
        // 如果提交了表单，WordPress 会添加 "settings-updated" 参数到 $_GET 里。
        if (!empty($_GET['settings-updated'])) {
            // 删除其它IndexNow密钥文件
            Ggpush_Common::ggpush_delete_indexnow_keyfile();
            // 添加更新信息
            add_settings_error('ggpush_messages', 'ggpush_message', '设置已保存。', 'updated');
            // 更新定时任务
            Ggpush_Common::ggpush_update_cron();
        }

        // 显示错误/更新信息
        settings_errors('ggpush_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                $page = Ggpush_Common::ggpush_get('page', 'ggpush_baidu_page');
                // 输出表单
                settings_fields($page);
                do_settings_sections($page);
                // 输出保存设置按钮
                submit_button('保存更改');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * 在插件页面添加设置链接
     *
     * @param $links
     *
     * @return mixed
     */
    public static function link_ggpush($links)
    {
        $settings_link = '<a href="admin.php?page=ggpush_base_page">设置</a>';
        $activate_link = '<a href="https://dev.ggdoc.cn/plugin/1.html" target="_blank">商业版</a>';
        array_unshift($links, $activate_link);
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * 在插件页面添加同名插件处理问题
     *
     * @param $links
     *
     * @return mixed
     */
    public static function duplicate_name_ggpush($links)
    {
        $settings_link = '<a href="https://dev.ggdoc.cn/plugin/1.html" target="_blank">请删除其它版本《果果推送》插件</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * 表单输入框回调
     *
     * @param array $args 这数据就是add_settings_field方法中第6个参数（$args）的数据
     */
    public static function ggpush_field_callback($args)
    {
        // 表单的id或name字段
        $id = $args['label_for'];
        // 表单的类型
        $form_type = isset($args['form_type']) ? $args['form_type'] : 'input';
        // 输入表单说明
        $form_desc = isset($args['form_desc']) ? $args['form_desc'] : '';
        // 输入表单type
        $type = isset($args['type']) ? $args['type'] : 'text';
        // 输入表单placeholder
        $form_placeholder = isset($args['form_placeholder']) ? $args['form_placeholder'] : '';
        // 下拉框等选项值
        $form_data = isset($args['form_data']) ? $args['form_data'] : array();
        // 表单的名称
        $input_name = 'ggpush_options[' . $id . ']';
        // 获取表单选项中的值
        $options = get_option('ggpush_options');
        // 表单的值
        $input_value = isset($options[$id]) ? $options[$id] : '';
        if (empty($input_value) && ('ggpush_indexnow_token' === $id || 'ggpush_sitemap_name' === $id)) {
            // 随机生成indexnow token
            $input_value = md5(get_home_url() . date('Y-m-d H:i:s') . mt_rand(1000, 9999));
        }
        $form_html = '';
        switch ($form_type) {
            case 'input':
                ?>
                <input id="<?php echo esc_attr($id); ?>" type="<?php echo esc_attr($type); ?>"
                       placeholder="<?php echo esc_attr($form_placeholder); ?>"
                       name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($input_value); ?>"
                       class="regular-text">
                <?php
                break;
            case 'select':
                ?>
                <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($input_name); ?>">
                    <?php
                    foreach ($form_data as $v) {
                        $selected = '';
                        if ($v['value'] == $input_value) {
                            $selected = 'selected';
                        }
                        ?>
                        <option <?php selected($selected, 'selected'); ?>
                                value="<?php echo esc_attr($v['value']); ?>"><?php echo esc_html($v['title']); ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
                break;
            case 'checkbox':
                ?>
                <fieldset><p>
                        <?php
                        $len = count($form_data);
                        foreach ($form_data as $k => $v) {
                            $checked = '';
                            if (!empty($input_value) && in_array($v['value'], $input_value)) {
                                $checked = 'checked';
                            }
                            ?>
                            <label>
                                <input type="checkbox" value="<?php echo esc_attr($v['value']); ?>"
                                       id="<?php echo esc_attr($id . '_' . $v['value']); ?>"
                                       name="<?php echo esc_attr($input_name . '[]'); ?>"
                                    <?php checked($checked, 'checked'); ?>><?php echo esc_html($v['title']); ?>
                            </label>
                            <?php
                            if ($k < ($len - 1)) {
                                ?>
                                <br>
                                <?php
                            }
                        }
                        ?>
                    </p></fieldset>
                <?php
                break;
            case 'textarea':
                ?>
                <textarea id="<?php echo esc_attr($id); ?>"
                          placeholder="<?php echo esc_attr($form_placeholder); ?>"
                          name="<?php echo esc_attr($input_name); ?>" class="large-text code"
                          rows="5"><?php echo esc_attr($input_value); ?></textarea>
                <?php
                break;
        }
        if (!empty($form_desc)) {
            ?>
            <p class="description"><?php echo esc_html($form_desc); ?></p>
            <?php
        }
    }

    /**
     * 统一设置保存变量
     * @param $input
     * @return array
     */
    public static function sanitize($input)
    {
        // 旧的保存数据
        $ggpush_options = get_option('ggpush_options');
        if (empty($ggpush_options)) {
            return $input;
        } else {
            return array_merge($ggpush_options, $input);
        }
    }

    /**
     * 发布新文章时推送链接
     *
     * @param $post_id
     * @param $post
     * @param $update
     *
     * @return void
     */
    public static function ggpush_to_publish($post_id, $post, $update)
    {
        if (!wp_is_post_revision($post) && $post->post_status === 'publish') {
            // 存在配置
            $ggpush_options = get_option('ggpush_options');
            if (!empty($ggpush_options)) {
                // 推送链接
                $post_url = get_permalink($post_id);
                if (!empty($ggpush_options['ggpush_baidu_token']) && $ggpush_options['ggpush_baidu_add_push'] == 1) {
                    Ggpush_Common::ggpush_push_baidu(array($post_url));
                }
                if (!empty($ggpush_options['ggpush_baidu_token']) && $ggpush_options['ggpush_baidu_add_fast_push'] == 1) {
                    Ggpush_Common::ggpush_push_baidu(array($post_url), true);
                }
                if (!empty($ggpush_options['ggpush_bing_token']) && $ggpush_options['ggpush_bing_add_push'] == 1) {
                    Ggpush_Common::ggpush_push_bing(array($post_url));
                }
                if (!empty($ggpush_options['ggpush_indexnow_token']) && $ggpush_options['ggpush_indexnow_add_push'] == 1) {
                    Ggpush_Common::ggpush_push_indexnow(array($post_url));
                }
            }
        }
    }
}