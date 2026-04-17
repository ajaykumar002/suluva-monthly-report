<?php
/**
 * Export Action class for Monthly Report plugin
 *
 * @package MonthlyReport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Monthly_Report_Export {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_export' ) );
    }

    /**
     * Handle CSV export
     */
    public function handle_export() {
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'monthly-report' && isset( $_GET['export'] ) && $_GET['export'] === 'csv' ) {
            $this->export_csv();
            exit;
        }
    }

    /**
     * Export report as CSV
     */
    private function export_csv() {
        // Prevent any output before headers
        if ( headers_sent() ) {
            wp_die( 'Headers already sent. Cannot export CSV.' );
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

        // Clear any previous output buffers
        while ( ob_get_level() ) {
            ob_end_clean();
        }

        // Set headers for CSV download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=monthly-report-' . $filter_month . '.csv' );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // Headers
        fputcsv( $output, array( 'Date', 'Breakfast Orders', 'Breakfast Sales', 'Lunch Orders', 'Lunch Sales', 'Dinner Orders', 'Dinner Sales', 'Total Orders', 'Total Sales' ) );

        // Data rows
        foreach ( $report_rows as $row ) {
            $breakfast_orders = intval( $row['breakfast_orders'] );
            $breakfast_sales = floatval( $row['breakfast_sales'] );
            $lunch_orders = intval( $row['lunch_orders'] );
            $lunch_sales = floatval( $row['lunch_sales'] );
            $dinner_orders = intval( $row['dinner_orders'] );
            $dinner_sales = floatval( $row['dinner_sales'] );
            $row_orders = $breakfast_orders + $lunch_orders + $dinner_orders;
            $row_sales = $breakfast_sales + $lunch_sales + $dinner_sales;

            fputcsv( $output, array(
                $row['order_date'],
                $breakfast_orders,
                $breakfast_sales,
                $lunch_orders,
                $lunch_sales,
                $dinner_orders,
                $dinner_sales,
                $row_orders,
                $row_sales
            ) );
        }

        // Totals
        fputcsv( $output, array(
            'Total',
            $total_breakfast_orders,
            $total_breakfast_sales,
            $total_lunch_orders,
            $total_lunch_sales,
            $total_dinner_orders,
            $total_dinner_sales,
            $total_orders,
            $total_sales
        ) );

        fclose( $output );
    }
}
