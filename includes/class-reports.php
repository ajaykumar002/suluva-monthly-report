<?php
/**
 * Reports Action class for Monthly Report plugin
 *
 * @package MonthlyReport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Monthly_Report_Reports {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Month Filter Reports', 'monthly-report' ),
            __( 'Month Filter Reports', 'monthly-report' ),
            'manage_woocommerce',
            'monthly-report',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
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
                    <a href="<?php echo esc_url( add_query_arg( array( 'export' => 'csv' ), admin_url( 'admin.php?page=monthly-report&month=' . urlencode( $filter_month ) ) ) ); ?>" class="button"><?php esc_html_e( 'Export CSV', 'monthly-report' ); ?></a>
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
}
