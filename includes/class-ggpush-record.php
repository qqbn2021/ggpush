<?php

/**
 * 推送记录
 */
class Ggpush_Record
{
    /**
     * 推送记录
     * @return void
     */
    public static function home()
    {
        $action = '';
        if (!empty($_GET['action'])) {
            $action = sanitize_title($_GET['action']);
        }
        if ('detail' === $action) {
            // 详情
            self::record_detail();
        } else if ('delete' === $action) {
            // 删除
            if (!empty($_GET['record_id'])) {
                $id = (int)$_GET['record_id'];
                self::record_delete($id);
            } else if (!empty($_GET['ids']) && is_array($_GET['ids'])) {
                $ids = array_map('absint', array_values(wp_unslash($_GET['ids'])));
                self::record_delete(0, $ids);
            } else {
                wp_die('删除失败');
            }
        } else if ('delete_1' === $action) {
            self::record_delete(0, array(), 1);
        } else if ('delete_3' === $action) {
            self::record_delete(0, array(), 3);
        } else if ('delete_30' === $action) {
            self::record_delete(0, array(), 30);
        } else if ('delete_all' === $action) {
            self::record_delete(0, array(), 0);
        } else {
            self::record_list();
        }
    }

    /**
     * 列表
     * @return void
     */
    public static function record_list()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">推送记录</h1>
            <form method="get">
                <input type="hidden" name="page" value="ggpush-record"/>
                <?php
                $ggpush_record_table = new Ggpush_Record_Table();
                $ggpush_record_table->prepare_items();
                $ggpush_record_table->display();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * 详情
     * @return void
     */
    public static function record_detail()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';
        $sql = 'select * from `' . $table_name . '` where `record_id` = %d limit 1';
        $query = $wpdb->prepare(
            $sql,
            intval(Ggpush_Plugin::get('record_id', 0))
        );
        $results = $wpdb->get_results($query, ARRAY_A);
        $record_data = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $record_data = $result;
            }
        }
        if (empty($record_data)) {
            wp_die('暂无数据');
        }
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">详情</h1>
            <br class="clear">
            <br class="clear">
            <table class="wp-list-table widefat fixed">
                <tbody>
                <tr>
                    <th scope="col">Id</th>
                    <td><?php echo esc_html($record_data['record_id']); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送平台</th>
                    <td><?php echo esc_html(Ggpush_Api::format_record_platform($record_data['record_platform'])); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送方式</th>
                    <td><?php echo esc_html(Ggpush_Api::format_record_mode($record_data['record_mode'])); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送链接数量</th>
                    <td><?php echo esc_html($record_data['record_num']); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送状态</th>
                    <td><?php echo esc_html(Ggpush_Api::format_result_status($record_data['record_result_status'])); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送结果状态码</th>
                    <td><?php echo esc_html($record_data['record_result_code']); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送时间</th>
                    <td><?php echo esc_html($record_data['record_date']); ?></td>
                </tr>
                </tbody>
            </table>
            <br class="clear">
            <table class="wp-list-table widefat fixed">
                <tr>
                    <th>推送链接</th>
                </tr>
                <tr>
                    <td>
                <textarea class="large-text code" readonly="readonly"
                          rows="5"><?php echo esc_html(implode(PHP_EOL, json_decode($record_data['record_urls'], true))); ?></textarea>
                    </td>
                </tr>
            </table>
            <br class="clear">
            <table class="wp-list-table widefat fixed">
                <tr>
                    <th>推送响应数据</th>
                </tr>
                <tr>
                    <td>
                <textarea class="large-text code" readonly="readonly"
                          rows="5"><?php echo esc_html($record_data['record_result']); ?></textarea>
                    </td>
                </tr>
            </table>
            <br class="clear">
            <?php
            if (!empty($record_data['record_result_error'])) {
                ?>
                <table class="wp-list-table widefat fixed">
                    <tr>
                        <th>失败原因</th>
                    </tr>
                    <tr>
                        <td>
                    <textarea class="large-text code" readonly="readonly"
                              rows="5"><?php echo esc_html($record_data['record_result_error']); ?></textarea>
                        </td>
                    </tr>
                </table>
                <br class="clear">
                <?php
            }
            ?>
            <a class="button button-primary" href="javascript:void(0);"
               onclick="history.back();">返回</a>
        </div>
        <?php
    }

    /**
     * 删除
     * @param int $id 删除单条记录
     * @param array $ids 删除多条记录
     * @param int $day 删除在此之前多少天的数据
     * @return void
     */
    public static function record_delete($id = 0, $ids = array(), $day = -1)
    {
        $current_url = self_admin_url('admin.php?page=ggpush-record');
        $result = false;
        $last_error = '非法操作';
        if (!empty($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-ggpush_records')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'ggpush_records';
            $query = '';
            if (!empty($id)) {
                $sql = 'DELETE FROM `' . $table_name . '` WHERE `record_id` = %d';
                $query = $wpdb->prepare(
                    $sql,
                    $id
                );
            } else if (!empty($ids)) {
                $sql = 'DELETE FROM `' . $table_name . '` WHERE `record_id` in (' . implode(', ', array_fill(0, count($ids), '%d')) . ')';
                $query = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $ids));
            } else if ($day >= 0) {
                if ($day <= 0) {
                    $end_record_date_time = time();
                } else if (1 === $day) {
                    $end_record_date_time = strtotime('-1 day');
                } else {
                    $end_record_date_time = strtotime('-' . $day . ' days');
                }
                $end_record_date = date('Y-m-d H:i:s', $end_record_date_time);
                $sql = 'DELETE FROM `' . $table_name . '` where `record_date` <= %s';
                $query = $wpdb->prepare(
                    $sql,
                    $end_record_date
                );
            } else {
                wp_die('删除失败');
            }
            if (!empty($query)) {
                $result = $wpdb->query($query);
                $last_error = $wpdb->last_error;
            }
        }
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">删除推送记录</h1>
            <p>
                <?php
                if ($result !== false) {
                    ?>
                    删除<?php echo esc_html($result); ?>条推送记录成功
                    <?php
                } else {
                    ?>
                    删除推送记录失败：<?php echo esc_html($last_error); ?>
                    <?php
                }
                ?>
            </p>
            <a class="button button-primary" href="<?php echo esc_url($current_url); ?>">返回</a>
        </div>
        <?php
    }
}