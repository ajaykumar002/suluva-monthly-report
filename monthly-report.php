<?php
/**
 * Plugin Name: WooCommerce Date Filter Reports
 * Plugin URI: https://example.com/
 * Description: Adds a WooCommerce admin report page that filters orders by date range.
 * Version: 1.0.0
 * Author: GitHub Copilot
 * Text Domain: monthly-report
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_dashboard_setup', 'monthly_report_add_dashboard_widget' );

function monthly_report_get_dashboard_data() {
    global $wpdb;

    $table_name = esc_sql( $wpdb->prefix . 'wc_orders' );
    $start_date = date( 'Y-m-d' );
    $end_date = date( 'Y-m-d' );

    $query = "
        SELECT
            id,
            payment_method,
            total_amount,
            TIME(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) AS order_time
        FROM {$table_name}
        WHERE status = %s
            AND DATE(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN %s AND %s
    ";

    $orders = $wpdb->get_results( $wpdb->prepare( $query, 'wc-completed', $start_date, $end_date ), ARRAY_A );

    $data = [];
    foreach ( $orders as $order ) {
        $method = $order['payment_method'];
        if ( ! $method ) {
            $wc_order = wc_get_order( $order['id'] );
            if ( $wc_order ) {
                $meta_value = $wc_order->get_meta( '_vtp_payment_list' );
                if ( $meta_value ) {
                    $method = is_array( $meta_value ) && isset( $meta_value[0]['name'] ) ? ($meta_value[0]['name'] == 'Others') ? __( 'UPI', 'monthly-report' ) : $meta_value[0]['name'] : __( 'Unknown', 'monthly-report' );
                } else {
                    $method = __( 'Unknown', 'monthly-report' );
                }
            } else {
                $method = __( 'Unknown', 'monthly-report' );
            }
        }

        $time = $order['order_time'];
        $amount = floatval( $order['total_amount'] );

        if ( $time >= '00:00:00' && $time < '12:30:00' ) {
            $data['breakfast'][$method]['count'] = ( $data['breakfast'][$method]['count'] ?? 0 ) + 1;
            $data['breakfast'][$method]['sales'] = ( $data['breakfast'][$method]['sales'] ?? 0 ) + $amount;
        } elseif ( $time >= '12:30:00' && $time < '16:00:00' ) {
            $data['lunch'][$method]['count'] = ( $data['lunch'][$method]['count'] ?? 0 ) + 1;
            $data['lunch'][$method]['sales'] = ( $data['lunch'][$method]['sales'] ?? 0 ) + $amount;
        } elseif ( $time >= '18:00:00' && $time < '23:30:00' ) {
            $data['dinner'][$method]['count'] = ( $data['dinner'][$method]['count'] ?? 0 ) + 1;
            $data['dinner'][$method]['sales'] = ( $data['dinner'][$method]['sales'] ?? 0 ) + $amount;
        }
    }

    return $data;
}

function monthly_report_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'monthly_report_dashboard_widget',
        __( 'Today Sales by Time Slot and Payment Method', 'monthly-report' ),
        'monthly_report_dashboard_widget_callback'
    );
}

function monthly_report_dashboard_widget_callback() {
    $data = monthly_report_get_dashboard_data();

    ?>
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; min-width: 200px;">
            <h4><?php esc_html_e( 'Breakfast Sales', 'monthly-report' ); ?></h4>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Payment Method', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Order Count', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Sales', 'monthly-report' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $data['breakfast'] ?? [] as $method => $info ) : ?>
                        <tr>
                            <td><?php echo esc_html( $method ); ?></td>
                            <td><?php echo esc_html( $info['count'] ?? 0 ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $info['sales'] ?? 0 ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <h4><?php esc_html_e( 'Lunch Sales', 'monthly-report' ); ?></h4>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Payment Method', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Order Count', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Sales', 'monthly-report' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $data['lunch'] ?? [] as $method => $info ) : ?>
                        <tr>
                            <td><?php echo esc_html( $method ); ?></td>
                            <td><?php echo esc_html( $info['count'] ?? 0 ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $info['sales'] ?? 0 ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <h4><?php esc_html_e( 'Dinner Sales', 'monthly-report' ); ?></h4>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Payment Method', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Order Count', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Sales', 'monthly-report' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $data['dinner'] ?? [] as $method => $info ) : ?>
                        <tr>
                            <td><?php echo esc_html( $method ); ?></td>
                            <td><?php echo esc_html( $info['count'] ?? 0 ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $info['sales'] ?? 0 ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

function monthly_report_check_woocommerce() {
    if ( ! is_admin() ) {
        return;
    }

    if ( current_user_can( 'activate_plugins' ) && ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'monthly_report_wc_missing_notice' );
    }
}

function monthly_report_wc_missing_notice() {
    echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce Date Filter Reports requires WooCommerce to be installed and active.', 'monthly-report' ) . '</p></div>';
}

function monthly_report_get_time_slot_report( $start_date, $end_date ) {
    global $wpdb;

    $table_name = esc_sql( $wpdb->prefix . 'wc_orders' );
    $query = "
        SELECT
            DATE(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) AS order_date,
    SUM(CASE 
        WHEN TIME(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN '07:00:00' AND '12:30:00' 
        THEN 1 ELSE 0 END) AS breakfast_orders,
    SUM(CASE 
        WHEN TIME(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN '07:00:00' AND '12:30:00' 
        THEN total_amount ELSE 0 END) AS breakfast_sales,

    SUM(CASE 
        WHEN TIME(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN '12:30:00' AND '16:00:00' 
        THEN 1 ELSE 0 END) AS lunch_orders,
    SUM(CASE 
        WHEN TIME(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN '12:30:00' AND '16:00:00' 
        THEN total_amount ELSE 0 END) AS lunch_sales,

    SUM(CASE 
        WHEN TIME(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN '18:00:00' AND '23:30:00' 
        THEN 1 ELSE 0 END) AS dinner_orders,
    SUM(CASE 
        WHEN TIME(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN '18:00:00' AND '23:30:00' 
        THEN total_amount ELSE 0 END) AS dinner_sales
        FROM {$table_name} AS orders
        WHERE status = %s
            AND DATE(CONVERT_TZ(date_created_gmt, '+00:00', '+05:30')) BETWEEN %s AND %s
        GROUP BY order_date
        ORDER BY order_date ASC
    ";

    return $wpdb->get_results( $wpdb->prepare( $query, 'wc-completed', $start_date, $end_date ), ARRAY_A );
}

function monthly_report_add_admin_menu() {
    add_submenu_page(
        'woocommerce',
        __( 'Date Filter Reports', 'monthly-report' ),
        __( 'Date Filter Reports', 'monthly-report' ),
        'manage_woocommerce',
        'monthly-report',
        'monthly_report_render_admin_page'
    );
}

function monthly_report_render_admin_page() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'WooCommerce is not active.', 'monthly-report' ) . '</p></div>';
        return;
    }

    $filter_month = isset( $_GET['month'] ) ? sanitize_text_field( wp_unslash( $_GET['month'] ) ) : date( 'Y-m' );

    if ( $filter_month ) {
        $month_date = DateTime::createFromFormat( 'Y-m', $filter_month );
    } else {
        $month_date = new DateTime();
    }

    if ( $month_date instanceof DateTime ) {
        $start_date = $month_date->format( 'Y-m-01' );
        $end_date   = $month_date->format( 'Y-m-t' );
    } else {
        $start_date = date( 'Y-m-01' );
        $end_date   = date( 'Y-m-t' );
    }

    $report_rows = monthly_report_get_time_slot_report( $start_date, $end_date );

    $total_days = count( $report_rows );
    $total_breakfast_orders = 0;
    $total_breakfast_sales = 0.0;
    $total_lunch_orders = 0;
    $total_lunch_sales = 0.0;
    $total_dinner_orders = 0;
    $total_dinner_sales = 0.0;
    $total_orders = 0;
    $total_sales = 0.0;

    foreach ( $report_rows as $row ) {
        $total_breakfast_orders += intval( $row['breakfast_orders'] );
        $total_breakfast_sales += floatval( $row['breakfast_sales'] );
        $total_lunch_orders += intval( $row['lunch_orders'] );
        $total_lunch_sales += floatval( $row['lunch_sales'] );
        $total_dinner_orders += intval( $row['dinner_orders'] );
        $total_dinner_sales += floatval( $row['dinner_sales'] );

        $total_orders += intval( $row['breakfast_orders'] ) + intval( $row['lunch_orders'] ) + intval( $row['dinner_orders'] );
        $total_sales += floatval( $row['breakfast_sales'] ) + floatval( $row['lunch_sales'] ) + floatval( $row['dinner_sales'] );
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'WooCommerce Time Slot Report', 'monthly-report' ); ?></h1>

        <form method="get" class="monthly-report-filter" style="margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <input type="hidden" name="page" value="monthly-report">

            <label for="monthly-report-month" style="display:block;">
                <span><?php esc_html_e( 'Month', 'monthly-report' ); ?></span>
                <input type="month" id="monthly-report-month" name="month" value="<?php echo esc_attr( $filter_month ); ?>" class="regular-text">
            </label>

            <p>
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Filter', 'monthly-report' ); ?></button>
                <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=monthly-report' ) ); ?>"><?php esc_html_e( 'Reset', 'monthly-report' ); ?></a>
            </p>
        </form>

        <div class="monthly-report-summary" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div class="monthly-report-card" style="padding: 16px; border: 1px solid #e1e1e1; background:#fff;">
                <strong><?php esc_html_e( 'Report Days', 'monthly-report' ); ?></strong>
                <div style="font-size: 1.8rem; margin-top: 8px;"><?php echo esc_html( $total_days ); ?></div>
            </div>
            <div class="monthly-report-card" style="padding: 16px; border: 1px solid #e1e1e1; background:#fff;">
                <strong><?php esc_html_e( 'Total Orders', 'monthly-report' ); ?></strong>
                <div style="font-size: 1.8rem; margin-top: 8px;"><?php echo esc_html( $total_orders ); ?></div>
            </div>
            <div class="monthly-report-card" style="padding: 16px; border: 1px solid #e1e1e1; background:#fff;">
                <strong><?php esc_html_e( 'Total Sales', 'monthly-report' ); ?></strong>
                <div style="font-size: 1.8rem; margin-top: 8px;"><?php echo wp_kses_post( wc_price( $total_sales ) ); ?></div>
            </div>
        </div>

        <?php if ( $total_days > 0 ) : ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Breakfast Orders', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Breakfast Sales', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Lunch Orders', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Lunch Sales', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Dinner Orders', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Dinner Sales', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Total Orders', 'monthly-report' ); ?></th>
                        <th><?php esc_html_e( 'Total Sales', 'monthly-report' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $report_rows as $row ) : ?>
                        <?php
                        $breakfast_orders = intval( $row['breakfast_orders'] );
                        $breakfast_sales = floatval( $row['breakfast_sales'] );
                        $lunch_orders = intval( $row['lunch_orders'] );
                        $lunch_sales = floatval( $row['lunch_sales'] );
                        $dinner_orders = intval( $row['dinner_orders'] );
                        $dinner_sales = floatval( $row['dinner_sales'] );
                        $row_orders = $breakfast_orders + $lunch_orders + $dinner_orders;
                        $row_sales = $breakfast_sales + $lunch_sales + $dinner_sales;
                        ?>
                        <tr>
                            <td><?php echo esc_html( $row['order_date'] ); ?></td>
                            <td><?php echo esc_html( $breakfast_orders ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $breakfast_sales ) ); ?></td>
                            <td><?php echo esc_html( $lunch_orders ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $lunch_sales ) ); ?></td>
                            <td><?php echo esc_html( $dinner_orders ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $dinner_sales ) ); ?></td>
                            <td><?php echo esc_html( $row_orders ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $row_sales ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php esc_html_e( 'Total', 'monthly-report' ); ?></th>
                        <th><?php echo esc_html( $total_breakfast_orders ); ?></th>
                        <th><?php echo wp_kses_post( wc_price( $total_breakfast_sales ) ); ?></th>
                        <th><?php echo esc_html( $total_lunch_orders ); ?></th>
                        <th><?php echo wp_kses_post( wc_price( $total_lunch_sales ) ); ?></th>
                        <th><?php echo esc_html( $total_dinner_orders ); ?></th>
                        <th><?php echo wp_kses_post( wc_price( $total_dinner_sales ) ); ?></th>
                        <th><?php echo esc_html( $total_orders ); ?></th>
                        <th><?php echo wp_kses_post( wc_price( $total_sales ) ); ?></th>
                    </tr>
                </tfoot>
            </table>
        <?php else : ?>
            <div class="notice notice-info">
                <p><?php esc_html_e( 'No report data found for the selected date range.', 'monthly-report' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
