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
        if (!empty($item[$column_name])) {
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

        return sprintf('%1$s %2$s', Ggpush_Common::format_record_platform($item['record_platform']), $this->row_actions($actions));
    }

    public function column_record_mode($item)
    {
        return Ggpush_Common::format_record_mode($item['record_mode']);
    }

    public function column_record_result_status($item)
    {
        return Ggpush_Common::format_result_status($item['record_result_status']);
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
        $sql = 'SELECT * FROM `' . $table_name . '` ORDER BY `record_id` DESC LIMIT %d, %d';
        $items = $wpdb->get_results($wpdb->prepare($sql, $offset, $per_page), ARRAY_A);
        $count = $wpdb->get_var('SELECT COUNT(`record_id`) FROM `' . $table_name . '`');
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
}