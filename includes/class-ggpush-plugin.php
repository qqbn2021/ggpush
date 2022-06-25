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
	`record_platform` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '推送平台：1 百度，2 360，3 搜狗，4 头条，5 神马，6 bing，7 谷歌，8 indexnow，9 yandex，10 Seznam.cz',
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
        // 如果表不存在才会执行创建
        maybe_create_table($table_name, $sql);
        // 更新定时任务
        Ggpush_Common::update_cron();
        // 创建默认配置
        add_option('ggpush_options', array(
            'menu_position' => 100,
            'push_timeout' => 30,
            'indexnow_token' => md5(time() . mt_rand(1000, 9999)),
            'sitemap_name' => md5(time() . mt_rand(1000, 9999)),
        ));
    }

    // 删除插件执行的代码
    public static function plugin_uninstall()
    {
        // 删除表
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';
        $wpdb->query('DROP TABLE IF EXISTS `' . $table_name . '`');
        // 删除其它IndexNow密钥文件
        Ggpush_Common::delete_indexnow_keyfile(true);
        // 删除配置
        delete_option('ggpush_options');
    }

    // 禁用插件执行的代码
    public static function plugin_deactivation()
    {
        // 插件已禁用，删除定时任务
        Ggpush_Common::update_cron(false);
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
        global $ggpush_options;
        if (!empty($ggpush_options['menu_position'])) {
            $position = (int)$ggpush_options['menu_position'];
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
            'ggpush-record',
            array('Ggpush_Record_Page', 'home')
        );

        // 计划任务页面
        add_submenu_page(
            '#ggpush',
            '定时任务',
            '定时任务',
            'manage_options',
            'ggpush-task-page',
            array('Ggpush_Task_Page', 'task_list')
        );

        // 基本设置页面
        add_submenu_page(
            '#ggpush',
            '基本设置',
            '基本设置',
            'manage_options',
            'ggpush-base-page',
            array('Ggpush_Plugin', 'show_page')
        );

        // 百度设置页面
        add_submenu_page(
            '#ggpush',
            '百度推送设置',
            '百度推送设置',
            'manage_options',
            'ggpush-baidu-page',
            array('Ggpush_Plugin', 'show_page')
        );

        // bing设置页面
        add_submenu_page(
            '#ggpush',
            '必应推送设置',
            '必应推送设置',
            'manage_options',
            'ggpush-bing-page',
            array('Ggpush_Plugin', 'show_page')
        );

        // indexnow设置页面
        add_submenu_page(
            '#ggpush',
            'IndexNow推送设置',
            'IndexNow推送设置',
            'manage_options',
            'ggpush-indexnow-page',
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
        if (!empty($_GET['settings-updated'])) {
            // 删除其它IndexNow密钥文件
            Ggpush_Common::delete_indexnow_keyfile();
            // 添加更新信息
            add_settings_error('ggpush_messages', 'ggpush_message', '设置已保存。', 'updated');
            // 更新定时任务
            Ggpush_Common::update_cron();
        }

        // 显示错误/更新信息
        settings_errors('ggpush_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                $page = Ggpush_Common::get('page', 'ggpush-baidu-page');
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
    public static function setups($links)
    {
        $business_link = '<a href="https://www.ggdoc.cn/plugin/1.html" target="_blank">商业版</a>';
        array_unshift($links, $business_link);
        $setups = '<a href="admin.php?page=ggpush-base-page">设置</a>';
        array_unshift($links, $setups);
        return $links;
    }

    /**
     * 在插件页面添加同名插件处理问题
     *
     * @param $links
     *
     * @return mixed
     */
    public static function duplicate_name($links)
    {
        $settings_link = '<a href="https://www.ggdoc.cn/plugin/1.html" target="_blank">请删除其它版本《果果推送》插件</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * 表单输入框回调
     *
     * @param array $args 这数据就是add_settings_field方法中第6个参数（$args）的数据
     */
    public static function field_callback($args)
    {
        // 表单的id或name字段
        $id = $args['label_for'];
        // 表单的名称
        $input_name = 'ggpush_options[' . $id . ']';
        // 获取表单选项中的值
        global $ggpush_options;
        // 表单的值
        $input_value = isset($ggpush_options[$id]) ? $ggpush_options[$id] : '';
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
        // 扩展form表单属性
        $form_extend = isset($args['form_extend']) ? $args['form_extend'] : array();
        switch ($form_type) {
            case 'input':
                self::generate_input(
                    array_merge(
                        array(
                            'id' => $id,
                            'type' => $type,
                            'placeholder' => $form_placeholder,
                            'name' => $input_name,
                            'value' => $input_value,
                            'class' => 'regular-text',
                        ),
                        $form_extend
                    ));
                break;
            case 'select':
                self::generate_select(
                    array_merge(
                        array(
                            'id' => $id,
                            'placeholder' => $form_placeholder,
                            'name' => $input_name
                        ),
                        $form_extend
                    ),
                    $form_data,
                    $input_value
                );
                break;
            case 'checkbox':
                self::generate_checkbox(
                    array_merge(
                        array(
                            'name' => $input_name . '[]'
                        ),
                        $form_extend
                    ),
                    $form_data,
                    $input_value
                );
                break;
            case 'textarea':
                self::generate_textarea(
                    array_merge(
                        array(
                            'id' => $id,
                            'placeholder' => $form_placeholder,
                            'name' => $input_name,
                            'class' => 'large-text code',
                            'rows' => 5,
                        ),
                        $form_extend
                    ),
                    $input_value
                );
                break;
        }
        if (!empty($form_desc)) {
            ?>
            <p class="description"><?php echo esc_html($form_desc); ?></p>
            <?php
        }
    }

    /**
     * 生成textarea表单
     * @param array $form_data 标签上的属性数组
     * @param string $value 默认值
     * @return void
     */
    public static function generate_textarea($form_data, $value = '')
    {
        ?><textarea <?php
        foreach ($form_data as $k => $v) {
            echo esc_attr($k); ?>="<?php echo esc_attr($v); ?>" <?php
        } ?>><?php echo esc_textarea($value); ?></textarea>
        <?php
    }

    /**
     * 生成checkbox表单
     * @param array $form_data 标签上的属性数组
     * @param array $checkboxs 下拉列表数据
     * @param string|array $value 选中值，单个选中字符串，多个选中数组
     * @return void
     */
    public static function generate_checkbox($form_data, $checkboxs, $value = '')
    {
        ?>
        <fieldset><p>
                <?php
                $len = count($checkboxs);
                foreach ($checkboxs as $k => $checkbox) {
                    $checked = '';
                    if (!empty($value)) {
                        if (is_array($value)) {
                            if (in_array($checkbox['value'], $value)) {
                                $checked = 'checked';
                            }
                        } else {
                            if ($checkbox['value'] == $value) {
                                $checked = 'checked';
                            }
                        }
                    }
                    ?>
                    <label>
                        <input type="checkbox" <?php checked($checked, 'checked'); ?><?php
                        foreach ($form_data as $k2 => $v2) {
                            echo esc_attr($k2); ?>="<?php echo esc_attr($v2); ?>" <?php
                        } ?> value="<?php echo esc_attr($checkbox['value']); ?>"
                        ><?php echo esc_html($checkbox['title']); ?>
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
    }

    /**
     * 生成input表单
     * @param array $form_data 标签上的属性数组
     * @return void
     */
    public static function generate_input($form_data)
    {
        ?><input <?php
        foreach ($form_data as $k => $v) {
            echo esc_attr($k); ?>="<?php echo esc_attr($v); ?>" <?php
        } ?>><?php
    }

    /**
     * 生成select表单
     * @param array $form_data 标签上的属性数组
     * @param array $selects 下拉列表数据
     * @param string|array $value 选中值，单个选中字符串，多个选中数组
     * @return void
     */
    public static function generate_select($form_data, $selects, $value = '')
    {
        ?><select <?php
        foreach ($form_data as $k => $v) {
            echo esc_attr($k); ?>="<?php echo esc_attr($v); ?>" <?php
        } ?>><?php
        foreach ($selects as $select) {
            $selected = '';
            if (!empty($value)) {
                if (is_array($value)) {
                    if (in_array($select['value'], $value)) {
                        $selected = 'selected';
                    }
                } else {
                    if ($select['value'] == $value) {
                        $selected = 'selected';
                    }
                }
            }
            ?>
            <option <?php selected($selected, 'selected'); ?>
                    value="<?php echo esc_attr($select['value']); ?>"><?php echo esc_html($select['title']); ?></option>
            <?php
        }
        ?>
        </select>
        <?php
    }

    /**
     * 统一设置保存变量
     * @param $input
     * @return array
     */
    public static function sanitize($input)
    {
        global $ggpush_options;
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
            global $ggpush_options;
            if (!empty($ggpush_options)) {
                // 推送链接
                $post_url = get_permalink($post_id);
                if (!empty($ggpush_options['baidu_token']) && $ggpush_options['baidu_add_push'] == 1) {
                    Ggpush_Common::push_baidu(array($post_url));
                }
                if (!empty($ggpush_options['baidu_token']) && $ggpush_options['baidu_add_fast_push'] == 1) {
                    Ggpush_Common::push_baidu(array($post_url), true);
                }
                if (!empty($ggpush_options['bing_token']) && $ggpush_options['bing_add_push'] == 1) {
                    Ggpush_Common::push_bing(array($post_url));
                }
                if (!empty($ggpush_options['indexnow_token']) && $ggpush_options['indexnow_add_push'] == 1) {
                    Ggpush_Common::push_indexnow(array($post_url));
                }
            }
        }
    }

    /**
     * 获取存储的设置值
     * @param string $option 设置的键
     * @param mixed $def_value 默认值
     * @return mixed|string
     */
    public static function get_option($option, $def_value = '')
    {
        global $ggpush_options;
        if (!empty($ggpush_options[$option])) {
            return $ggpush_options[$option];
        }
        return $def_value;
    }
}