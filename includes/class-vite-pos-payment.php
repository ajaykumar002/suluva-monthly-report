<?php
/**
 * Vite-POS Payment Method Updation class for Monthly Report plugin
 *
 * @package MonthlyReport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Monthly_Report_Vite_POS_Payment {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_payment_admin_menu' ) );
        add_action( 'wp_ajax_update_order_payment_method', array( $this, 'ajax_update_payment_method' ) );
        add_action( 'wp_ajax_get_order_payment_method', array( $this, 'ajax_get_payment_method' ) );
    }

    /**
     * Add Vite-POS Payment admin menu
     */
    public function add_payment_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Vite-POS Payment Update', 'monthly-report' ),
            __( 'Vite-POS Payment Update', 'monthly-report' ),
            'manage_woocommerce',
            'vite-pos-payment',
            array( $this, 'render_payment_admin_page' )
        );
    }

    /**
     * Render Vite-POS Payment admin page
     */
    public function render_payment_admin_page() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'WooCommerce is not active.', 'monthly-report' ) . '</p></div>';
            return;
        }

        $search_order_id = isset( $_GET['search_order'] ) ? sanitize_text_field( wp_unslash( $_GET['search_order'] ) ) : '';
        $order_data = null;

        if ( $search_order_id ) {
            $order_data = $this->get_order_payment_data( intval( $search_order_id ) );
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Vite-POS Payment Method Update', 'monthly-report' ); ?></h1>

            <div class="payment-update-section" style="margin-bottom: 24px; padding: 20px; background: #fff; border: 1px solid #e1e1e1;">
                <h2><?php esc_html_e( 'Search & Update Payment Method', 'monthly-report' ); ?></h2>

                <form method="get" class="payment-search-form" style="margin-bottom: 20px;">
                    <input type="hidden" name="page" value="vite-pos-payment">

                    <label for="payment-search-order-id" style="display: block; margin-bottom: 10px;">
                        <strong><?php esc_html_e( 'Order ID', 'monthly-report' ); ?></strong>
                        <input type="number" id="payment-search-order-id" name="search_order" value="<?php echo esc_attr( $search_order_id ); ?>" class="regular-text" min="1">
                    </label>

                    <p>
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Search', 'monthly-report' ); ?></button>
                        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vite-pos-payment' ) ); ?>"><?php esc_html_e( 'Reset', 'monthly-report' ); ?></a>
                    </p>
                </form>

                <?php if ( $order_data && $order_data['success'] ) : ?>
                    <div class="order-details" style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-left: 4px solid #0073aa;">
                        <h3><?php esc_html_e( 'Order Details', 'monthly-report' ); ?></h3>

                        <table class="widefat" style="margin-top: 10px;">
                            <tr>
                                <td><strong><?php esc_html_e( 'Order ID', 'monthly-report' ); ?>:</strong></td>
                                <td><?php echo esc_html( $order_data['order_id'] ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e( 'Order Date', 'monthly-report' ); ?>:</strong></td>
                                <td><?php echo esc_html( $order_data['order_date'] ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e( 'Customer', 'monthly-report' ); ?>:</strong></td>
                                <td><?php echo esc_html( $order_data['customer_name'] ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e( 'Order Status', 'monthly-report' ); ?>:</strong></td>
                                <td><?php echo esc_html( $order_data['order_status'] ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e( 'Order Total', 'monthly-report' ); ?>:</strong></td>
                                <td><?php echo wp_kses_post( wc_price( $order_data['order_total'] ) ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e( 'Current Payment Method', 'monthly-report' ); ?>:</strong></td>
                                <td><?php echo esc_html( $order_data['current_payment_method'] ); ?></td>
                            </tr>
                        </table>

                        <?php if ( ! empty( $order_data['order_meta'] ) ) : ?>
                            <div style="margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
                                <h4><?php esc_html_e( 'Order Metadata', 'monthly-report' ); ?></h4>
                                <table class="widefat" style="margin-top: 10px;">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Meta Key', 'monthly-report' ); ?></th>
                                            <th><?php esc_html_e( 'Meta Value', 'monthly-report' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $order_data['order_meta'] as $meta_key => $meta_value ) : ?>
                                            <tr>
                                                <td><strong><?php echo esc_html( $meta_key ); ?></strong></td>
                                                <td>
                                                    <?php
                                                    if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
                                                        echo '<pre>' . esc_html( wp_json_encode( $meta_value, JSON_PRETTY_PRINT ) ) . '</pre>';
                                                    } else {
                                                        echo esc_html( $meta_value );
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd;">
                            <h4><?php esc_html_e( 'Update Payment Method', 'monthly-report' ); ?></h4>

                            <form id="payment-update-form" style="margin-top: 10px;">
                                <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_data['order_id'] ); ?>">
                                <input type="hidden" name="action" value="update_order_payment_method">
                                <?php wp_nonce_field( 'update_payment_nonce', 'nonce' ); ?>

                                <label for="payment-method-select" style="display: block; margin-bottom: 10px;">
                                    <strong><?php esc_html_e( 'New Payment Method', 'monthly-report' ); ?></strong>
                                </label>

                                <select id="payment-method-select" name="payment_method" class="regular-text" required>
                                    <option value=""><?php esc_html_e( 'Select Payment Method', 'monthly-report' ); ?></option>
                                    <option value="cash"><?php esc_html_e( 'Cash', 'monthly-report' ); ?></option>
                                    <option value="others"><?php esc_html_e( 'Others', 'monthly-report' ); ?></option>
                                </select>

                                <p style="margin-top: 10px;">
                                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Update Payment Method', 'monthly-report' ); ?></button>
                                </p>
                            </form>

                            <div id="update-message" style="margin-top: 10px;"></div>
                        </div>
                    </div>
                <?php elseif ( $search_order_id ) : ?>
                    <div class="notice notice-error">
                        <p><?php esc_html_e( 'Order not found. Please check the Order ID and try again.', 'monthly-report' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            document.getElementById('payment-update-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.getElementById('update-message');
                    if (data.success) {
                        messageDiv.innerHTML = '<div class="notice notice-success is-dismissible"><p>' + data.data.message + '</p></div>';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        messageDiv.innerHTML = '<div class="notice notice-error"><p>' + data.data.message + '</p></div>';
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Get order payment data
     *
     * @param int $order_id Order ID.
     * @return array Order data.
     */
    public function get_order_payment_data( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return array( 'success' => false );
        }

        // Get order meta data
        $order_meta = array();
        
        // Get all post meta for this order
        

        // Determine payment method
        $current_payment_method = $order->get_payment_method_title() ? $order->get_payment_method_title() : $order->get_payment_method();
        
        // Debug: Log order meta data
        error_log( 'Order Meta for Order ID ' . $order_id . ': ' . wp_json_encode( $order->get_meta('_vtp_payment_list') ) );
        
        // Check if this is a VitePOS order
        $is_vitepos_meta = $order->get_meta('_is_vitepos');
        $is_vitepos = isset( $is_vitepos_meta ) ? $is_vitepos_meta : '';
        
        $vtp_payment_list_meta = $order->get_meta('_vtp_payment_list');
        if ( $is_vitepos === 'Y' && isset( $vtp_payment_list_meta ) ) {
            $vtp_payment_list = $vtp_payment_list_meta;
            
            // Check if it's an array and has at least one element
            if ( is_array( $vtp_payment_list ) && ! empty( $vtp_payment_list ) && isset( $vtp_payment_list[0]['name'] ) ) {
                $current_payment_method = $vtp_payment_list[0]['name'];
                
                // Special handling for "Others" payment method
                if ( $current_payment_method === 'Others' ) {
                    $current_payment_method = __( 'UPI', 'monthly-report' );
                }
            }
        }

        return array(
            'success'                   => true,
            'order_id'                  => $order_id,
            'order_date'                => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
            'customer_name'             => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'order_status'              => $order->get_status(),
            'order_total'               => $order->get_total(),
            'current_payment_method'    => $current_payment_method,
            'order_meta'                => $order_meta,
        );
    }

    /**
     * AJAX: Update payment method
     */
    public function ajax_update_payment_method() {
        check_ajax_referer( 'update_payment_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'monthly-report' ) ) );
        }

        $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
        $payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';

        if ( ! $order_id || ! $payment_method ) {
            wp_send_json_error( array( 'message' => __( 'Invalid order ID or payment method.', 'monthly-report' ) ) );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => __( 'Order not found.', 'monthly-report' ) ) );
        }

        // Store old payment method for logging
        $old_payment_method = $order->get_payment_method();

        // Check if this is a VitePOS order
        $is_vitepos_meta = $order->get_meta('_is_vitepos');
        $is_vitepos = isset( $is_vitepos_meta ) ? $is_vitepos_meta : '';

        // Update payment method
        

        // For VitePOS orders, also update the _vtp_payment_list meta if it exists
        if ( $is_vitepos === 'Y' ) {
            $vtp_payment_list_meta = $order->get_meta('_vtp_payment_list');
            $vtp_payment_list = isset( $vtp_payment_list_meta ) ? $vtp_payment_list_meta : array();

            // Update the payment list based on selected method, preserving other fields
            if ( is_array( $vtp_payment_list ) && ! empty( $vtp_payment_list ) ) {
                if ( $payment_method === 'others' ) {
                    $vtp_payment_list[0]['type'] = 'O';
                    $vtp_payment_list[0]['name'] = 'Others';
                } elseif ( $payment_method === 'cash' ) {
                    $vtp_payment_list[0]['type'] = 'C';
                    $vtp_payment_list[0]['name'] = 'Cash';
                }

                // Update the VitePOS payment list meta
                $order->update_meta_data( '_vtp_payment_list', $vtp_payment_list );
            }else {
                $order->set_payment_method( $payment_method );
            }
            // If no existing array, do not create new meta
        }

        $order->save();

        // Add order note
        $order->add_order_note(
            sprintf(
                __( 'Payment method updated from %s to %s by admin.', 'monthly-report' ),
                $old_payment_method,
                $payment_method
            )
        );

        wp_send_json_success(
            array(
                'message' => sprintf(
                    __( 'Payment method updated successfully for Order #%d', 'monthly-report' ),
                    $order_id
                ),
            )
        );
    }

    /**
     * AJAX: Get payment method
     */
    public function ajax_get_payment_method() {
        check_ajax_referer( 'get_payment_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'monthly-report' ) ) );
        }

        $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid order ID.', 'monthly-report' ) ) );
        }

        $order_data = $this->get_order_payment_data( $order_id );

        if ( $order_data['success'] ) {
            wp_send_json_success( $order_data );
        } else {
            wp_send_json_error( array( 'message' => __( 'Order not found.', 'monthly-report' ) ) );
        }
    }
}
