<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * 推送记录表类
 */
class Ggpush_Record_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'ggpush_record',
            'plural' => 'ggpush_records',
            'ajax' => false
        ));
    }

    public function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'record_id' => 'Id',
            'record_platform' => '推送平台',
            'record_mode' => '推送方式',
            'record_num' => '推送链接数量',
            'record_result_status' => '推送状态',
            'record_result_code' => '推送结果状态码',
            'record_date' => '推送时间'
        );
    }

    public function column_default($item, $column_name)
    {
        if (isset($item[$column_name])) {
            return $item[$column_name];
        }
        return '';
    }

    public function column_record_platform($item)
    {
        $page = sanitize_title($_REQUEST['page']);
        $actions = array(
            'detail' => sprintf('<a href="?page=%s&action=%s&record_id=%d&_wpnonce=%s">详情</a>', $page, 'detail', $item['record_id'], wp_create_nonce('bulk-ggpush_records')),
            'delete' => sprintf('<a href="?page=%s&action=%s&record_id=%d&_wpnonce=%s">删除</a>', $page, 'delete', $item['record_id'], wp_create_nonce('bulk-ggpush_records')),
        );

        return sprintf('%1$s %2$s', Ggpush_Api::format_record_platform($item['record_platform']), $this->row_actions($actions));
    }

    public function column_record_mode($item)
    {
        return Ggpush_Api::format_record_mode($item['record_mode']);
    }

    public function column_record_result_status($item)
    {
        return Ggpush_Api::format_result_status($item['record_result_status']);
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%d" />', $item['record_id']
        );
    }

    public function get_bulk_actions()
    {
        return array(
            'delete' => '删除勾选的数据',
            'delete_1' => '删除1天之前的数据',
            'delete_3' => '删除3天之前的数据',
            'delete_30' => '删除30天之前的数据',
            'delete_all' => '删除所有的数据'
        );
    }

    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->_args['plural'];
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $per_page = 10;
        $current_page = $this->get_pagenum();
        if (1 < $current_page) {
            $offset = $per_page * ($current_page - 1);
        } else {
            $offset = 0;
        }
        $map = array();
        if (!empty($_GET['record_platform'])) {
            $map[] = '`record_platform` = ' . intval($_GET['record_platform']);
        }
        if (!empty($_GET['record_mode'])) {
            $map[] = '`record_mode` = ' . intval($_GET['record_mode']);
        }
        if (!empty($_GET['record_status'])) {
            $map[] = '`record_result_status` = ' . intval($_GET['record_status']);
        }
        if (isset($_GET['record_code']) && is_numeric($_GET['record_code'])) {
            $map[] = '`record_result_code` = ' . intval($_GET['record_code']);
        }
        $where = '';
        if (!empty($map)) {
            $where = 'WHERE ' . implode(' AND ', $map);
        }
        $sql = 'SELECT * FROM `' . $table_name . '` ' . $where . ' ORDER BY `record_id` DESC LIMIT %d, %d';
        $items = $wpdb->get_results($wpdb->prepare($sql, $offset, $per_page), ARRAY_A);
        $count = $wpdb->get_var('SELECT COUNT(`record_id`) FROM `' . $table_name . '` ' . $where);
        $this->items = $items;
        $this->set_pagination_args(array(
            'total_items' => $count,
            'per_page' => $per_page,
            'total_pages' => ceil($count / $per_page)
        ));
    }

    /**
     * 没有数据
     * @return void
     */
    public function no_items()
    {
        ?>
        暂无推送记录
        <?php
    }

    public function extra_tablenav($which)
    {
        if ('top' === $which) {
            $record_platform = Ggpush_Plugin::get('record_platform', 0);
            $record_mode = Ggpush_Plugin::get('record_mode', 0);
            $record_status = Ggpush_Plugin::get('record_status', 0);
            $record_code = Ggpush_Plugin::get('record_code', '');
            ?>
            <div class="alignleft actions">
                <label for="filter-by-platform" class="screen-reader-text">按推送平台筛选</label>
                <select name="record_platform" id="filter-by-platform">
                    <option value="0" <?php selected($record_platform,0);?>>推送平台</option>
                    <option value="1" <?php selected($record_platform,1);?>>百度</option>
                    <option value="2" <?php selected($record_platform,2);?>>360</option>
                    <option value="3" <?php selected($record_platform,3);?>>搜狗</option>
                    <option value="4" <?php selected($record_platform,4);?>>头条</option>
                    <option value="5" <?php selected($record_platform,5);?>>神马</option>
                    <option value="6" <?php selected($record_platform,6);?>>必应</option>
                    <option value="7" <?php selected($record_platform,7);?>>谷歌</option>
                    <option value="8" <?php selected($record_platform,8);?>>IndexNow</option>
                    <option value="9" <?php selected($record_platform,9);?>>Yandex</option>
                    <option value="10" <?php selected($record_platform,10);?>>Seznam.cz</option>
                </select>
                <label for="filter-by-mode" class="screen-reader-text">按推送方式筛选</label>
                <select name="record_mode" id="filter-by-mode">
                    <option value="0" <?php selected($record_mode,0);?>>推送方式</option>
                    <option value="1" <?php selected($record_mode,1);?>>普通收录</option>
                    <option value="2" <?php selected($record_mode,2);?>>快速收录</option>
                    <option value="3" <?php selected($record_mode,3);?>>Js提交</option>
                    <option value="4" <?php selected($record_mode,4);?>>Api提交</option>
                    <option value="5" <?php selected($record_mode,5);?>>IndexNow</option>
                </select>
                <label for="filter-by-status" class="screen-reader-text">按推送状态筛选</label>
                <select name="record_status" id="filter-by-status">
                    <option value="0" <?php selected($record_status,0);?>>推送状态</option>
                    <option value="1" <?php selected($record_status,1);?>>成功</option>
                    <option value="2" <?php selected($record_status,2);?>>失败</option>
                    <option value="3" <?php selected($record_status,3);?>>未知</option>
                </select>
                <label for="filter-by-code" class="screen-reader-text">按推送结果状态码筛选</label>
                <select name="record_code" id="filter-by-code">
                    <option value="" <?php selected($record_code,'');?>>推送结果状态码</option>
                    <option value="0" <?php selected($record_code,0);?>>0</option>
                    <option value="200" <?php selected($record_code,200);?>>200</option>
                    <option value="202" <?php selected($record_code,202);?>>202</option>
                    <option value="301" <?php selected($record_code,301);?>>301</option>
                    <option value="302" <?php selected($record_code,302);?>>302</option>
                    <option value="400" <?php selected($record_code,400);?>>400</option>
                    <option value="401" <?php selected($record_code,401);?>>401</option>
                    <option value="403" <?php selected($record_code,403);?>>403</option>
                    <option value="404" <?php selected($record_code,404);?>>404</option>
                    <option value="405" <?php selected($record_code,405);?>>405</option>
                    <option value="413" <?php selected($record_code,413);?>>413</option>
                    <option value="422" <?php selected($record_code,422);?>>422</option>
                    <option value="500" <?php selected($record_code,500);?>>500</option>
                    <option value="502" <?php selected($record_code,502);?>>502</option>
                    <option value="503" <?php selected($record_code,503);?>>503</option>
                </select>
                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="筛选">
            </div>
            <?php
        }
    }
}