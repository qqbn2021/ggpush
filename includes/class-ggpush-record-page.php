<?php

/**
 * 定时任务
 */
class Ggpush_Record_Page
{
    public static function home()
    {
        // 推送记录详情
        if (!empty($_GET['record_id'])) {
            self::record_detail();
        } else if (isset($_GET['ggpush_clear_day']) && wp_verify_nonce($_GET['ggpushnonce'], 'delete_ggpush_record')) {
            self::record_delete();
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
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';
        $current_url = self_admin_url('admin.php?page=ggpush_record');
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">推送记录</h1>
            <form method="get">
                <input type="hidden" name="ggpushnonce"
                       value="<?php echo esc_attr(wp_create_nonce('delete_ggpush_record')); ?>">
                <input type="hidden" name="page" value="ggpush_record">
                <div class="tablenav top">
                    <div class="alignleft">
                        <label for="bulk-action-selector-top"
                               class="screen-reader-text">选择批量操作</label>
                        <select name="ggpush_clear_day">
                            <option value="-1">清除推送记录</option>
                            <option value="1">1天之前</option>
                            <option value="3">3天之前</option>
                            <option value="30">30天之前</option>
                            <option value="0">所有</option>
                        </select>
                        <input type="submit" class="button" value="应用">
                    </div>
                    <br class="clear">
                </div>
                <table class="wp-list-table widefat fixed">
                    <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">推送平台</th>
                        <th scope="col">推送方式</th>
                        <th scope="col">推送链接数量</th>
                        <th scope="col">推送状态</th>
                        <th scope="col">推送结果状态码</th>
                        <th scope="col">推送时间</th>
                        <th scope="col">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $size = 10;
                    $total = $wpdb->get_var('SELECT COUNT(*) FROM `' . $table_name . '`');
                    $results = array();
                    if (empty($total)) {
                        $total = 0;
                    } else {
                        $total = intval($total);
                        $total_paged = ceil($total / $size);
                        $paged = (int)Ggpush_Common::ggpush_get('paged', 1);
                        if ($paged < 1) {
                            $paged = 1;
                        }
                        if ($paged > $total_paged) {
                            $paged = $total_paged;
                        }
                        $prev_page = $paged - 1;
                        $next_page = $paged + 1;
                        if ($prev_page < 1) {
                            $prev_page = 1;
                        }
                        if ($next_page > $total_paged) {
                            $next_page = $total_paged;
                        }
                        $start = ($paged - 1) * $size;
                        $table_name = $wpdb->prefix . 'ggpush_records';
                        $sql = 'select `record_id`,`record_platform`,`record_mode`,`record_num`,`record_result_status`,`record_result_code`,`record_date` from `' . $table_name . '` order by `record_id` desc limit %d,%d';
                        $query = $wpdb->prepare(
                            $sql,
                            $start,
                            $size
                        );
                        $results = $wpdb->get_results($query, 'ARRAY_A');
                    }
                    if (!empty($results)) {
                        foreach ($results as $result) {
                            ?>
                            <tr>
                                <td><?php echo esc_html($result['record_id']); ?></td>
                                <td><?php echo esc_html(Ggpush_Common::ggpush_format_record_platform($result['record_platform'])); ?></td>
                                <td><?php echo esc_html(Ggpush_Common::ggpush_format_record_mode($result['record_mode'])); ?></td>
                                <td><?php echo esc_html($result['record_num']); ?></td>
                                <td><?php echo esc_html(Ggpush_Common::ggpush_format_result_status($result['record_result_status'])); ?></td>
                                <td><?php echo esc_html($result['record_result_code']); ?></td>
                                <td><?php echo esc_html($result['record_date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url($current_url . '&record_id=' . $result['record_id']); ?>">详情</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="8">暂无数据</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
                <?php
                if ($total > 0) {
                    ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages"><span
                                    class="displaying-num"><?php echo esc_html($total); ?>条记录</span>
                            <span class="pagination-links">
                    <a class="first-page button" href="<?php echo esc_url($current_url); ?>">
                        <span class="screen-reader-text">首页</span>
                        <span aria-hidden="true">«</span>
                    </a>
                    <a class="prev-page button" href="<?php echo esc_url($current_url . '&paged=' . $prev_page); ?>">
                        <span class="screen-reader-text">上一页</span>
                        <span aria-hidden="true">‹</span>
                    </a>
                    <span class="screen-reader-text">当前页</span>
                        <span id="table-paging" class="paging-input">
                            <?php echo esc_html(sprintf('第%1$s页，共%2$s页', $paged, $total_paged)); ?>
                        </span>
                    </span>
                            <a class="next-page button"
                               href="<?php echo esc_url($current_url . '&paged=' . $next_page); ?>">
                                <span class="screen-reader-text">下一页</span>
                                <span aria-hidden="true">›</span>
                            </a>
                            <a class="last-page button"
                               href="<?php echo esc_url($current_url . '&paged=' . $total_paged); ?>">
                                <span class="screen-reader-text">末页</span>
                                <span aria-hidden="true">»</span>
                            </a>
                            </span>
                        </div>
                        <br class="clear">
                    </div>
                    <?php
                }
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
        $current_url = self_admin_url('admin.php?page=ggpush_record');
        $sql         = 'select * from `' . $table_name . '` where `record_id` = %d limit 1';
        $query       = $wpdb->prepare(
            $sql,
            intval( Ggpush_Common::ggpush_get( 'record_id', 0 ) )
        );
        $results     = $wpdb->get_results( $query, ARRAY_A );
        $record_data = array();
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                $record_data = $result;
            }
        }
        if ( empty( $record_data ) ) {
            wp_die( '暂无数据' );
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
                    <td><?php echo esc_html( $record_data['record_id'] ); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送平台</th>
                    <td><?php echo esc_html(Ggpush_Common::ggpush_format_record_platform( $record_data['record_platform'] ) ); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送方式</th>
                    <td><?php echo esc_html(Ggpush_Common::ggpush_format_record_mode( $record_data['record_mode'] ) ); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送链接数量</th>
                    <td><?php echo esc_html( $record_data['record_num'] ); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送状态</th>
                    <td><?php echo esc_html(Ggpush_Common::ggpush_format_result_status( $record_data['record_result_status'] ) ); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送结果状态码</th>
                    <td><?php echo esc_html( $record_data['record_result_code'] ); ?></td>
                </tr>
                <tr>
                    <th scope="col">推送时间</th>
                    <td><?php echo esc_html( $record_data['record_date'] ); ?></td>
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
                          rows="5"><?php echo esc_html( implode( PHP_EOL, json_decode( $record_data['record_urls'], true ) ) ); ?></textarea>
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
                          rows="5"><?php echo esc_html( $record_data['record_result'] ); ?></textarea>
                    </td>
                </tr>
            </table>
            <br class="clear">
            <?php
            if ( ! empty( $record_data['record_result_error'] ) ) {
                ?>
                <table class="wp-list-table widefat fixed">
                    <tr>
                        <th>失败原因</th>
                    </tr>
                    <tr>
                        <td>
                    <textarea class="large-text code" readonly="readonly"
                              rows="5"><?php echo esc_html( $record_data['record_result_error'] ); ?></textarea>
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
     * @return void
     */
    public static function record_delete()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ggpush_records';
        $current_url = self_admin_url('admin.php?page=ggpush_record');
        $ggpush_clear_day = (int)$_GET['ggpush_clear_day'];
        if ($ggpush_clear_day <= 0) {
            $end_record_date_time = time();
        } else if (1 === $ggpush_clear_day) {
            $end_record_date_time = strtotime('-1 day');
        } else {
            $end_record_date_time = strtotime('-' . $ggpush_clear_day . ' days');
        }
        $end_record_date = date('Y-m-d H:i:s', $end_record_date_time);
        $sql = 'DELETE FROM `' . $table_name . '` where `record_date` <= %s';
        $query = $wpdb->prepare(
            $sql,
            $end_record_date
        );
        $result = $wpdb->query($query);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">删除推送记录</h1>
            <p>
                <?php
                if ($result !== false) {
                    ?>
                    删除<?php echo esc_html($result);?>条推送记录成功
                    <?php
                } else {
                    ?>
                    删除推送记录失败：<?php echo esc_html($wpdb->last_error); ?>
                    <?php
                }
                ?>
            </p>
            <a class="button button-primary" href="<?php echo esc_url($current_url); ?>">返回</a>
        </div>
        <?php
    }
}