<?php
/**
 * Dashboard Action class for Monthly Report plugin
 *
 * @package MonthlyReport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Monthly_Report_Dashboard {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
    }

    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'monthly_report_dashboard_widget',
            __( 'Today Sales by Time Slot and Payment Method', 'monthly-report' ),
            array( $this, 'render_widget' )
        );
    }

    /**
     * Render dashboard widget
     */
    public function render_widget() {
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
}
