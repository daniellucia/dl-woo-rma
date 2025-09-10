<?php 

namespace DL\RMA;

defined('ABSPATH') || exit;

class Form
{
    /**
     * Renderiza el formulario de RMA en pasos
     * @param array $atts
     * @return string
     * @author Daniel Lucia
     */
    public function render($atts)
    {
        if (!isset($_GET['rma'])) {
            return '';
        }
 
        $order_id = intval($_GET['rma']);
        if ($order_id <= 0) {
            return '';
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return '<p>' . __('Invalid order.', 'dl-woo-rma') . '</p>';
        }

        // Paso actual
        $step = isset($_GET['rma_step']) ? intval($_GET['rma_step']) : 1;

        echo '<h2>' . sprintf(__('RMA Request for Order #%d', 'dl-woo-rma'), $order_id) . '</h2>';

        if ($step === 1) {

            echo $this->render_product_selection_step($order);

        } elseif ($step === 2 && !empty($_GET['rma_products'])) {
            
            if (!$this->validate_nonce($_GET['dl_woo_rma_nonce_step1'], 'dl_woo_rma_step1')) {
                return '<p style="color:red;">' . __('Security check failed. Please try again.', 'dl-woo-rma') . '</p>';
            }

            $selected_products = array_map('intval', (array)$_GET['rma_products']);
            echo $this->render_action_step($order, $selected_products);

        } elseif ($step === 3 && isset($_GET['rma_submit'])) {
            
            if (!$this->validate_nonce($_GET['dl_woo_rma_nonce_step3'], 'dl_woo_rma_step3')) {
                return '<p style="color:red;">' . __('Security check failed. Please try again.', 'dl-woo-rma') . '</p>';
            }

            $rma = new RMA();
            $selected_products = isset($_GET['rma_products']) ? array_map('intval', (array)$_GET['rma_products']) : [];
            $order_id = $order->get_id();
            $customer_id = $order->get_customer_id();
            $reason = isset($_GET['rma_action']) ? sanitize_text_field($_GET['rma_action']) : '';
            $comments = isset($_GET['rma_comment']) ? sanitize_textarea_field($_GET['rma_comment']) : '';

            foreach($selected_products as $product_id) {
                $rma_id = $rma->create($order_id, $customer_id, $product_id, $reason, $comments);
                if ($rma_id === 0) {
                    echo '<p style="color:red;">' . __('An RMA request for one of the selected products already exists.', 'dl-woo-rma') . '</p>';
                } else {
                    echo '<p>' . sprintf(__('RMA request created for product ID %d with RMA ID %d.', 'dl-woo-rma'), $product_id, $rma_id) . '</p>';
                }
            }

        } else {
            
            echo $this->render_product_selection_step($order, true);

        }

        return '';
    }

    /**
     * Paso 1: Selección de productos
     * @param mixed $order
     * @param mixed $error
     * @return bool|string
     * @author Daniel Lucia
     */
    private function render_product_selection_step($order, $error = false)
    {
        ob_start();
        ?>
        <form method="get" class="dl-form-rma">

            <?php do_action('dl_woo_rma_before_product_selection', $order); ?>

            <?php wp_nonce_field('dl_woo_rma_step1', 'dl_woo_rma_nonce_step1'); ?>
            <input type="hidden" name="rma" value="<?php echo esc_attr($order->get_id()); ?>" />

            <?php if ($error): ?>
                <p class="dl-error-product-empty"><?php _e('You must select at least one product.', 'dl-woo-rma'); ?></p>
            <?php endif; ?>

            <p class="form-row">
                <label><?php _e('Select the products you wish to process:', 'dl-woo-rma'); ?></label>
                <?php
                foreach ($order->get_items() as $item_id => $item) {
                    if (!is_a($item, 'WC_Order_Item_Product')) continue;
                    $product = $item->get_product();
                    if (!$product) continue;
                    $product_name = $product->get_name();
                    $qty = $item->get_quantity();
                    echo '<label style="display:block;margin-bottom:4px;">';
                        echo '<input type="checkbox" name="rma_products[]" value="' . esc_attr($item_id) . '"> ';
                        echo esc_html($product_name) . ' &times; ' . intval($qty);
                    echo '</label>';
                }
                ?>
            </p>
            
            <?php do_action('dl_woo_rma_after_product_selection', $order); ?>

            <input type="hidden" name="rma_step" value="2" />
            <button type="submit" class="woocommerce-button wp-element-button button"><?php _e('Siguiente', 'dl-woo-rma'); ?></button>

        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Paso 2: Acción y comentarios
     * @param mixed $order
     * @param mixed $selected_products
     * @return bool|string
     * @author Daniel Lucia
     */
    private function render_action_step($order, $selected_products)
    {
        ob_start();

        $selected_products = apply_filters('dl_woo_rma_validate_selected_products', $selected_products, $order);
        
        ?>
        <form method="get" class="dl-form-rma">

            <?php do_action('dl_woo_rma_before_action_selection', $order, $selected_products); ?>

            <?php wp_nonce_field('dl_woo_rma_step3', 'dl_woo_rma_nonce_step3'); ?>
            <input type="hidden" name="rma_step" value="3" />
            <input type="hidden" name="rma" value="<?php echo esc_attr($order->get_id()); ?>" />
            <?php
            foreach ($selected_products as $item_id) {
                echo '<input type="hidden" name="rma_products[]" value="' . esc_attr($item_id) . '" />';
            }
            ?>
            <p class="form-row">
                <label><?php _e('Available Action', 'dl-woo-rma'); ?></label>
                <select name="rma_action">
                    <?php
                    $rules = explode("\n", get_option('dl_woo_rma_rules', "15|Devolución\n60|Cambio\n1095|Garantía"));
                    $order_date = strtotime($order->get_date_created());
                    $days = floor((time() - $order_date) / DAY_IN_SECONDS);
                    foreach ($rules as $rule) {
                        list($limit, $action) = array_map('trim', explode('|', $rule));
                        if ($days <= intval($limit)) {
                            echo '<option value="' . esc_attr($action) . '">' . esc_html($action) . '</option>';
                        }
                    }
                    ?>
                </select>
            </p>
            <p class="form-row">
                <label><?php _e('Comment', 'dl-woo-rma'); ?></label>
                <textarea name="rma_comment" class="input-text"></textarea>
            </p>

            <?php do_action('dl_woo_rma_after_action_selection', $order, $selected_products); ?>
            <button type="submit" name="rma_submit" class="woocommerce-button wp-element-button button"><?php _e('Generate RMA', 'dl-woo-rma'); ?></button>

        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Valida el nonce para seguridad
     * @param mixed $nonce
     * @param mixed $action
     * @return bool
     * @author Daniel Lucia
     */
    private function validate_nonce($nonce, $action) {
        if (!isset($nonce) || !wp_verify_nonce($nonce, $action)) {
            return false;
        }
        return true;
    }
}
