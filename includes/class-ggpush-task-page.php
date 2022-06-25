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
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html('定时任务'); ?></h1>
            <br class="clear">
            <p>为了确保定时任务稳定运行，您需要定时访问：<a href="<?php echo esc_url(get_home_url() . '/wp-cron.php'); ?>"
                                       target="_blank"><?php echo esc_url(get_home_url() . '/wp-cron.php'); ?></a></p>
            <form method="get">
                <input type="hidden" name="page" value="ggpush-task-page"/>
                <?php
                $ggpush_task_table = new Ggpush_Task_Table();
                $ggpush_task_table->prepare_items();
                $ggpush_task_table->display();
                ?>
            </form>
        </div>
        <?php
    }
}