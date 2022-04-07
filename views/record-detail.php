<?php
// 直接访问报404错误
if ( ! function_exists( 'add_action' ) ) {
	http_response_code( 404 );
	exit;
}
$table_name  = $wpdb->prefix . 'ggpush_records';
$sql         = 'select * from `' . $table_name . '` where `record_id` = ' . intval( $_GET['record_id'] ) . ' limit 1';
$results     = $wpdb->get_results( $sql, 'ARRAY_A' );
$record_data = [];
if ( ! empty( $results ) ) {
	foreach ( $results as $result ) {
		$record_data = $result;
	}
}
if ( empty( $record_data ) ) {
	wp_die( __( 'No data', 'ggpush' ) );
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Push record', 'ggpush' ); ?></h1>
    <br class="clear">
    <br class="clear">
    <table class="wp-list-table widefat fixed">
        <tbody>
        <tr>
            <th scope="col">Id</th>
            <td><?php echo $record_data['record_id']; ?></td>
        </tr>
        <tr>
            <th scope="col"><?php _e( 'Push platform', 'ggpush' ); ?></th>
            <td><?php echo ggpush_format_record_platform( $record_data['record_platform'] ); ?></td>
        </tr>
        <tr>
            <th scope="col"><?php _e( 'Push method', 'ggpush' ); ?></th>
            <td><?php echo ggpush_format_record_mode( $record_data['record_mode'] ); ?></td>
        </tr>
        <tr>
            <th scope="col"><?php _e( 'Number of push links', 'ggpush' ); ?></th>
            <td><?php echo $record_data['record_num']; ?></td>
        </tr>
        <tr>
            <th scope="col"><?php _e( 'Push status', 'ggpush' ); ?></th>
            <td><?php echo ggpush_format_result_status( $record_data['record_result_status'] ); ?></td>
        </tr>
        <tr>
            <th scope="col"><?php _e( 'Push result status code', 'ggpush' ); ?></th>
            <td><?php echo $record_data['record_result_code']; ?></td>
        </tr>
        <tr>
            <th scope="col"><?php _e( 'Push time', 'ggpush' ); ?></th>
            <td><?php echo $record_data['record_date']; ?></td>
        </tr>
        </tbody>
    </table>
    <br class="clear">
    <table class="wp-list-table widefat fixed">
        <tr>
            <th><?php _e( 'Push links', 'ggpush' ); ?></th>
        </tr>
        <tr>
            <td>
                <textarea style="width: 100% !important;" readonly="readonly" rows="5"><?php echo implode(PHP_EOL,json_decode($record_data['record_urls'],true)); ?></textarea>
            </td>
        </tr>
    </table>
    <br class="clear">
    <table class="wp-list-table widefat fixed">
        <tr>
            <th><?php _e( 'Push response data', 'ggpush' ); ?></th>
        </tr>
        <tr>
            <td>
                <textarea style="width: 100% !important;" readonly="readonly" rows="5"><?php echo $record_data['record_result']; ?></textarea>
            </td>
        </tr>
    </table>
    <br class="clear">
    <?php
        if (!empty($record_data['record_result_error'])){
            ?>
            <table class="wp-list-table widefat fixed">
                <tr>
                    <th><?php _e( 'Reason for failure', 'ggpush' ); ?></th>
                </tr>
                <tr>
                    <td>
                        <textarea style="width: 100% !important;" readonly="readonly" rows="5"><?php echo $record_data['record_result_error']; ?></textarea>
                    </td>
                </tr>
            </table>
            <br class="clear">

            <?php
        }
    ?>
    <a class="button button-primary" href="javascript:void(0);"
       onclick="history.back();"><?php _e( 'Go back', 'ggpush' ); ?></a>
</div>