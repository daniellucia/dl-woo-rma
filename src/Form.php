<?php 

namespace DL\RMA;

defined('ABSPATH') || exit;

class Form
{
    /**
     * Renderiza el formulario de RMA
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

        ?>
        <h2><?php printf(__('RMA Request for Order #%d', 'dl-woo-rma'), $order_id); ?></h2>

        <form method="post">

            <p class="form-row">
                <label><?php _e('Select the products you wish to process:', 'dl-woo-rma'); ?></label>
                <?php
                foreach ($order->get_items() as $item_id => $item) {

                    if (!is_a($item, 'WC_Order_Item_Product')) {
                        continue;
                    }
                    
                    $product = $item->get_product();
                    if (!$product) { 
                        continue;
                    }

                    $product_name = $product->get_name();
                    $product_id = $product->get_id();
                    $qty = $item->get_quantity();
                    echo '<label style="display:block;margin-bottom:4px;">';
                        echo '<input type="checkbox" name="rma_products[]" value="' . esc_attr($item_id) . '"> ';
                        echo esc_html($product_name) . ' &times; ' . intval($qty);
                    echo '</label>';
                }
                ?>
            </p>

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
                <textarea name="rma_comment" required class="input-text"></textarea>
            </p>

            <p class="form-row">
                <button type="submit" name="rma_submit" class="woocommerce-button wp-element-button button"><?php _e('Generate RMA', 'dl-woo-rma'); ?></button>
            </p>
            
        </form>
        <?php

        return '';

    }
}
