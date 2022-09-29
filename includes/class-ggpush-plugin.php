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
CREATE TABLE {$table_name} (
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
) {$charset_collate};
SQL;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        // 如果表不存在才会执行创建
        maybe_create_table($table_name, $sql);
        // 创建默认配置
        global $ggpush_options;
        if (empty($ggpush_options)) {
            add_option('ggpush_options', array(
                'menu_position' => 100,
                'push_timeout' => 30,
            ));
        }
    }

    // 删除插件执行的代码
    public static function plugin_uninstall()
    {
        // 删除表
        global $wpdb;
        global $ggpush_options;
        $ggpush_options = get_option('ggpush_options', array());
        $table_name = $wpdb->prefix . 'ggpush_records';
        $wpdb->query('DROP TABLE IF EXISTS `' . $table_name . '`');
        // 删除IndexNow密钥文件
        Ggpush_Api::delete_indexnow_keyfile();
        // 删除配置
        delete_option('ggpush_options');
    }

    // 禁用插件执行的代码
    public static function plugin_deactivation()
    {
        // 插件已禁用，删除定时任务
        Ggpush_Cron::update_cron(false);
    }

    // 初始化
    public static function admin_init()
    {
        // 注册设置页面
        if (!empty($_REQUEST['page'])) {
            if ('ggpush-timing-push' === $_REQUEST['page']) {
                // 定时推送
                Ggpush_Task::init_page();
            } else if ('ggpush-settings' === $_REQUEST['page']) {
                // 推送设置
                Ggpush_Settings::init_page();
            } else if ('ggpush-assist-push' === $_REQUEST['page']) {
                // 辅助推送
                Ggpush_Assist::init_page();
            }
        }
    }

    // 添加菜单
    public static function admin_menu()
    {
        global $ggpush_options;
        $position = null;
        if (!empty($ggpush_options['menu_position'])) {
            $position = intval($ggpush_options['menu_position']);
        }
        $push_record = 0;
        if (!empty($ggpush_options['push_record'])) {
            $push_record = intval($ggpush_options['push_record']);
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
        if (0 === $push_record) {
            add_submenu_page(
                '#ggpush',
                '推送记录',
                '推送记录',
                'manage_options',
                'ggpush-record',
                array('Ggpush_Record', 'home')
            );
        }

        // 推送设置
        add_submenu_page(
            '#ggpush',
            '推送设置',
            '推送设置',
            'manage_options',
            'ggpush-settings',
            array('Ggpush_Settings', 'show_page')
        );

        // 定时推送
        add_submenu_page(
            '#ggpush',
            '定时推送',
            '定时推送',
            'manage_options',
            'ggpush-timing-push',
            array('Ggpush_Task', 'show_page')
        );

        // 辅助推送
        add_submenu_page(
            '#ggpush',
            '辅助推送',
            '辅助推送',
            'manage_options',
            'ggpush-assist-push',
            array('Ggpush_Assist', 'show_page')
        );

        remove_submenu_page('#ggpush', '#ggpush');
    }

    /**
     * 在插件页面添加设置链接
     * @param $links
     * @return mixed
     */
    public static function setups($links)
    {
        $business_link = '<a href="https://www.ggdoc.cn/plugin/1.html" target="_blank">商业版</a>';
        array_unshift($links, $business_link);

        $setups = '<a href="admin.php?page=ggpush-settings">设置</a>';
        array_unshift($links, $setups);

        return $links;
    }

    /**
     * 表单输入框回调
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
                            'name' => $input_name . '[]',
                            'input_name' => $input_name,
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
        <fieldset>
            <p>
                <input type="hidden" name="<?php echo esc_attr($form_data['input_name']); ?>" value="">
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
            </p>
        </fieldset>
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
        if (empty($input)) {
            return $ggpush_options;
        }
        // 更新indexnow密钥文件
        if (isset($input['indexnow_token'])) {
            Ggpush_Api::update_indexnow_keyfile($input['indexnow_token'], isset($ggpush_options['indexnow_token']) ? $ggpush_options['indexnow_token'] : '');
        }
        if (empty($ggpush_options)) {
            return $input;
        } else {
            return array_merge($ggpush_options, $input);
        }
    }

    /**
     * 更新配置
     * @param array $options
     * @return void
     */
    public static function update_options($options)
    {
        global $ggpush_options;
        $ggpush_options = self::sanitize($options);
        update_option('ggpush_options', $ggpush_options);
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

    /**
     * 获取GET中的参数值
     * @param string $name
     * @param mixed $defValue
     * @return mixed
     */
    public static function get($name, $defValue = '')
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        return $defValue;
    }

    /**
     * 设置上一次错误信息
     * @param string $error
     * @return void
     */
    public static function set_error($error)
    {
        global $ggpush_error;
        $ggpush_error = $error;
    }

    /**
     * 获取上一次的错误信息
     * @return string
     */
    public static function get_error()
    {
        global $ggpush_error;
        return $ggpush_error;
    }

    /**
     * 添加JavaScript文件
     * @return void
     */
    public static function admin_enqueue_scripts()
    {
        global $ggpush_options;
        // 发布文章后推送
        if (!empty($ggpush_options['publish_article_platform'])) {
            self::push_after_post();
        }
    }

    /**
     * 发布文章后推送处理的js
     * @return void
     */
    public static function push_after_post()
    {
        global $pagenow;
        if (!empty($pagenow)) {
            // 发布文章后推送
            if ('post.php' === $pagenow) {
                // 添加静态文件
                wp_enqueue_script(
                    'ggpush-sync',
                    plugins_url('/js/ggpush_publish.min.js', GGPUSH_PLUGIN_FILE),
                    array('jquery'),
                    '0.0.4',
                    true
                );
                wp_localize_script(
                    'ggpush-sync',
                    'ggpush_obj',
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('ggpush'),
                    )
                );
            } else if ('post-new.php' === $pagenow) {
                global $post;
                $post_id = 0;
                if (!empty($post->ID)) {
                    $post_id = $post->ID;
                } else if (!empty($_GET['post'])) {
                    $post_id = intval($_GET['post']);
                }
                set_transient('ggpush_publish_post_id', $post_id);
            }
        }
    }

    /**
     * 发布文章后推送
     * @return void
     */
    public static function wp_ajax_ggpush_publish()
    {
        check_ajax_referer('ggpush');
        global $ggpush_options;
        if (empty($ggpush_options['publish_article_platform'])) {
            wp_send_json(array(
                'status' => 0,
                'msg' => '未开启发布文章后推送',
            ));
        }
        $post_id = get_transient('ggpush_publish_post_id');
        if (empty($post_id)) {
            wp_send_json(array(
                'status' => 0,
                'msg' => '非发布的新文章',
            ));
        }
        if (!empty($post_id) && !wp_is_post_revision($post_id) && 'publish' === get_post_status($post_id)) {
            // 推送链接
            $post_url = get_permalink($post_id);
            Ggpush_Api::platform_push($post_url, $ggpush_options['publish_article_platform']);
            delete_transient('ggpush_publish_post_id');
            wp_send_json(array(
                'url' => $post_url,
                'status' => 1,
                'msg' => '链接已推送',
            ));
        }
        wp_send_json(array(
            'status' => 0,
            'msg' => '链接推送失败',
        ));
    }
}