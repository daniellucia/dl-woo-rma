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