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

            if (!is_a($item, 'WC_Order_Item_Product')) {
                continue;
            }

            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $product_name = $product->get_name();
            $qty = $item->get_quantity();

            for ($i = 0; $i < $qty; $i++) {
                echo '<label style="display:block;margin-bottom:4px;">';

                    $value = ['i' => $i, 'id' => $product->get_id()];
                    $disabled = $rma->productExistsInRMA($order->get_id(), $value['id'], $value['i']);

                    $value = base64_encode(wp_json_encode($value));
                    echo '<input type="checkbox" name="rma_products[]" value="' . $value . '" ' . ($disabled ? 'disabled' : '') . '> ';
                    echo esc_html($product_name);

                echo '</label>';
            }
        }
        ?>
    </p>

    <?php do_action('dl_woo_rma_after_product_selection', $order); ?>

    <input type="hidden" name="rma_step" value="2" />
    <button type="submit" class="woocommerce-button wp-element-button button"><?php _e('Siguiente', 'dl-woo-rma'); ?></button>

</form>