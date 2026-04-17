<?php
/**
 * Initialize Monthly Report Plugin
 *
 * @package MonthlyReport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load plugin files and initialize classes
 */
function monthly_report_init() {
    // Load helper functions
    require_once plugin_dir_path( __FILE__ ) . 'helpers.php';

    // Load action classes
    require_once plugin_dir_path( __FILE__ ) . 'class-dashboard.php';
    require_once plugin_dir_path( __FILE__ ) . 'class-reports.php';
    require_once plugin_dir_path( __FILE__ ) . 'class-export.php';
    require_once plugin_dir_path( __FILE__ ) . 'class-vite-pos-payment.php';

    // Initialize classes
    new Monthly_Report_Dashboard();
    new Monthly_Report_Reports();
    new Monthly_Report_Export();
    new Monthly_Report_Vite_POS_Payment();
}

add_action( 'plugins_loaded', 'monthly_report_init' );

/**
 * Check if WooCommerce is active
 */
function monthly_report_check_woocommerce() {
    if ( ! is_admin() ) {
        return;
    }

    if ( current_user_can( 'activate_plugins' ) && ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'monthly_report_wc_missing_notice' );
    }
}

add_action( 'admin_init', 'monthly_report_check_woocommerce' );

/**
 * Display WooCommerce missing notice
 */
function monthly_report_wc_missing_notice() {
    echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce Date Filter Reports requires WooCommerce to be installed and active.', 'monthly-report' ) . '</p></div>';
}
