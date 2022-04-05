<?php
// 直接访问报404错误
if ( ! function_exists( 'add_action' ) ) {
	http_response_code( 404 );
	exit;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Push record', 'ggpush' ); ?></h1>
    <form method="get">
        <input type="hidden" name="ggpushnonce" value="<?php echo wp_create_nonce( 'delete_ggpush_record' ); ?>">
        <input type="hidden" name="page" value="ggpush_record">
        <div class="tablenav top">
            <div class="alignleft">
                <label for="bulk-action-selector-top"
                       class="screen-reader-text"><?php _e( 'Select bulk action', 'ggpush' ); ?></label>
                <select name="ggpush_clear_day">
                    <option value="-1"><?php _e( 'Clear push record', 'ggpush' ); ?></option>
                    <option value="1"><?php _e( '1 day ago', 'ggpush' ); ?></option>
                    <option value="3"><?php _e( '3 days ago', 'ggpush' ); ?></option>
                    <option value="30"><?php _e( '30 days ago', 'ggpush' ); ?></option>
                    <option value="0"><?php _e( 'All', 'ggpush' ); ?></option>
                </select>
                <input type="submit" class="button" value="<?php _e( 'Apply', 'ggpush' ); ?>">
            </div>
            <br class="clear">
        </div>
        <table class="wp-list-table widefat fixed">
            <thead>
            <tr>
                <th scope="col">Id</th>
                <th scope="col"><?php _e( 'Push platform', 'ggpush' ); ?></th>
                <th scope="col"><?php _e( 'Push method', 'ggpush' ); ?></th>
                <th scope="col"><?php _e( 'Number of push links', 'ggpush' ); ?></th>
                <th scope="col"><?php _e( 'Push status', 'ggpush' ); ?></th>
                <th scope="col"><?php _e( 'Push result status code', 'ggpush' ); ?></th>
                <th scope="col"><?php _e( 'Push time', 'ggpush' ); ?></th>
                <th scope="col"><?php _e( 'Actions', 'ggpush' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			$size    = 10;
			$total   = 0;
			$sql     = 'select count(*) as `total` from `' . $table_name . '`';
			$results = $wpdb->get_results( $sql, 'ARRAY_A' );
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					$total = $result['total'];
				}
			}
			$total_paged = ceil( $total / $size );
			$paged       = (int) ( $_GET['paged'] ?? 1 );
			if ( $paged < 1 ) {
				$paged = 1;
			}
			if ( $paged > $total_paged ) {
				$paged = $total_paged;
			}
			$prev_page = $paged - 1;
			$next_page = $paged + 1;
			if ( $prev_page < 1 ) {
				$prev_page = 1;
			}
			if ( $next_page > $total_paged ) {
				$next_page = $total_paged;
			}
			$start      = ( $paged - 1 ) * $size;
			$table_name = $wpdb->prefix . 'ggpush_records';
			$sql        = 'select `record_id`,`record_platform`,`record_mode`,`record_num`,`record_result_status`,`record_result_code`,`record_date` from `' . $table_name . '` order by `record_id` desc limit ' . $start . ',' . $size;
			$results    = $wpdb->get_results( $sql, 'ARRAY_A' );
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					echo '<tr>';
					echo '<td>' . $result['record_id'] . '</td>';
					echo '<td>' . ggpush_format_record_platform( $result['record_platform'] ) . '</td>';
					echo '<td>' . ggpush_format_record_mode( $result['record_mode'] ) . '</td>';
					echo '<td>' . $result['record_num'] . '</td>';
					echo '<td>' . ggpush_format_result_status( $result['record_result_status'] ) . '</td>';
					echo '<td>' . $result['record_result_code'] . '</td>';
					echo '<td>' . $result['record_date'] . '</td>';
					echo '<td><a href="' . $current_url . '&record_id=' . $result['record_id'] . '" target="_blank">' . __( 'Details', 'ggpush' ) . '</a></td>';
					echo '</tr>';
				}
			} else {
				echo '<tr><td colspan="8">' . __( 'No data', 'ggpush' ) . '</td></tr>';
			}
			?>
            </tbody>
        </table>
        <?php
            if ($total > 0){
        ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages"><span
                        class="displaying-num"><?php echo $total . ' ' . __( 'items', 'ggpush' ); ?></span>
                <span class="pagination-links">
                    <a class="first-page button" href="<?php echo $current_url; ?>">
                        <span class="screen-reader-text"><?php _e( 'Front Page', 'ggpush' ); ?></span><span
                                aria-hidden="true">«</span>
                    </a>
                    <a class="prev-page button" href="<?php echo $current_url . '&paged=' . $prev_page; ?>">
                        <span class="screen-reader-text"><?php _e( 'Previous Page', 'ggpush' ); ?></span><span
                                aria-hidden="true">‹</span>
                    </a>
                    <span class="screen-reader-text"><?php _e( 'Current Page', 'ggpush' ); ?></span>
                        <span id="table-paging" class="paging-input">
                            <?php echo sprintf( __( '%1$s of %2$s', 'ggpush' ), $paged, $total_paged ); ?>
                        </span>
                    </span>
                <a class="next-page button" href="<?php echo $current_url . '&paged=' . $next_page; ?>">
                    <span class="screen-reader-text"><?php _e( 'Next', 'ggpush' ); ?></span><span
                            aria-hidden="true">›</span>
                </a>
                <a class="last-page button" href="<?php echo $current_url . '&paged=' . $total_paged; ?>">
                    <span class="screen-reader-text"><?php _e( 'Last page', 'ggpush' ); ?></span><span
                            aria-hidden="true">»</span>
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
