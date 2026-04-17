<?php
/**
 * Helper functions for Monthly Report plugin
 *
 * @package MonthlyReport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get time slot report data
 *
 * @param string $start_date Start date (Y-m-d format).
 * @param string $end_date   End date (Y-m-d format).
 * @return array Report data.
 */
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

/**
 * Get dashboard data for today
 *
 * @return array Dashboard data organized by meal times and payment methods.
 */
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
