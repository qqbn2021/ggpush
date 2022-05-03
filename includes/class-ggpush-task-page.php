<?php

/**
 * 定时任务
 */
class Ggpush_Task_Page
{
    /**
     * 显示定时任务
     * @return void
     */
    public static function task_list()
    {
        // 展示定时任务执行情况
        $ggpush_crons = array(
            'ggpush_run_baidu_cron' => array(
                'title' => '百度搜索引擎普通收录',
                'cron_time' => wp_next_scheduled('ggpush_run_baidu_cron')
            ),
            'ggpush_run_baidu_fast_cron' => array(
                'title' => '百度搜索引擎快速收录',
                'cron_time' => wp_next_scheduled('ggpush_run_baidu_fast_cron')
            ),
            'ggpush_run_bing_cron' => array(
                'title' => '必应搜索引擎推送',
                'cron_time' => wp_next_scheduled('ggpush_run_bing_cron')
            ),
            'ggpush_run_indexnow_cron' => array(
                'title' => 'IndexNow推送',
                'cron_time' => wp_next_scheduled('ggpush_run_indexnow_cron')
            )
        );
        ?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html('定时任务'); ?></h1>
    <br class="clear">
    <p>为了确保定时任务稳定运行，您需要定时访问：<a href="<?php echo esc_url(get_home_url().'/wp-cron.php');?>" target="_blank"><?php echo esc_url(get_home_url().'/wp-cron.php');?></a></p>
    <table class="wp-list-table widefat fixed">
        <thead>
        <tr>
            <th>任务名称</th>
            <th>状态</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($ggpush_crons as $ggpush_cron) {
            if (empty($ggpush_cron['cron_time'])) {
                $ggpush_cron_str = '未运行';
            } else {
                $ggpush_cron_str = '下次运行时间：' . date('Y-m-d H:i:s', $ggpush_cron['cron_time']);
            }
            ?>
            <tr>
                <td><?php echo esc_html($ggpush_cron['title']);?></td>
                <td><?php echo esc_html($ggpush_cron_str);?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
        <?php
    }
}