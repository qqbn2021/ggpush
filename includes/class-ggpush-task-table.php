<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * 定时任务类
 */
class Ggpush_Task_Table extends WP_List_Table
{

    public function get_columns()
    {
        return array(
            'title' => '任务名称',
            'interval' => '推送间隔',
            'num' => '每次推送链接数量',
            'type' => '推送方式',
            'status' => '状态'
        );
    }

    public function column_default($item, $column_name)
    {
        if (!empty($item[$column_name])) {
            return $item[$column_name];
        }
        return '';
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = array(
            array(
                'title' => '百度搜索引擎普通收录',
                'interval' => Ggpush_Plugin::get_option('baidu_interval'),
                'num' => Ggpush_Plugin::get_option('baidu_num'),
                'type' => Ggpush_Plugin::get_option('baidu_type'),
                'status' => wp_next_scheduled('ggpush_run_baidu_cron')
            ),
            array(
                'title' => '百度搜索引擎快速收录',
                'interval' => Ggpush_Plugin::get_option('baidu_fast_interval'),
                'num' => Ggpush_Plugin::get_option('baidu_fast_num'),
                'type' => Ggpush_Plugin::get_option('baidu_fast_type'),
                'status' => wp_next_scheduled('ggpush_run_baidu_fast_cron')
            ),
            array(
                'title' => '必应搜索引擎推送',
                'interval' => Ggpush_Plugin::get_option('bing_interval'),
                'num' => Ggpush_Plugin::get_option('bing_num'),
                'type' => Ggpush_Plugin::get_option('bing_type'),
                'status' => wp_next_scheduled('ggpush_run_bing_cron')
            ),
            array(
                'title' => 'IndexNow推送',
                'interval' => Ggpush_Plugin::get_option('indexnow_interval'),
                'num' => Ggpush_Plugin::get_option('indexnow_num'),
                'type' => Ggpush_Plugin::get_option('indexnow_type'),
                'status' => wp_next_scheduled('ggpush_run_indexnow_cron')
            )
        );
        $this->set_pagination_args(array(
            'total_items' => count($this->items),
            'per_page' => count($this->items),
            'total_pages' => 1
        ));
    }

    /**
     * 格式化状态
     * @param $item
     * @return string
     */
    public static function column_status($item)
    {
        if (empty($item['status'])) {
            return '未运行';
        } else {
            return '下次运行时间：' . date('Y-m-d H:i:s', $item['status']);
        }
    }

    public static function column_interval($item)
    {
        if (empty($item['interval'])) {
            return '';
        } else {
            return $item['interval'] . '分钟';
        }
    }

    public static function column_type($item)
    {
        if (empty($item['type'])) {
            return '';
        } else {
            switch ($item['type']) {
                case 1:
                    return '最新';
                case 2:
                    return '随机';
                case 3:
                    return '伪随机';
            }
            return $item['type'];
        }
    }

    public static function column_num($item)
    {
        if (empty($item['num'])) {
            return '';
        } else {
            return $item['num'] . '条';
        }
    }
}