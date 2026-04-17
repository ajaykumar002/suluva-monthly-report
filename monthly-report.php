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

// Load plugin initialization
require_once plugin_dir_path( __FILE__ ) . 'includes/init.php';

