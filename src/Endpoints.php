<?php

namespace DL\RMA;

defined('ABSPATH') || exit;

class Endpoints
{
    /**
     * Añade un enlace "Tramitar RMA" en la página de pedidos
     * @param mixed $actions
     * @param mixed $order
     * @author Daniel Lucia
     */
    public function add_rma_link($actions, $order)
    {
        $order_id = $order->get_id();
        $url = add_query_arg(['rma' => $order_id], wc_get_account_endpoint_url('orders'));
        $actions['rma'] = [
            'url'  => $url,
            'name' => __('Process RMA', 'dl-woo-rma')
        ];
        return $actions;
    }

    /**
     * Renderiza el formulario de RMA
     * @param array $atts
     * @return string
     * @author Daniel Lucia
     */
    public function render_rma_form($atts)
    {
        if (!isset($_GET['rma'])) return '';
        $order_id = intval($_GET['rma']);
        $order = wc_get_order($order_id);
        if (!$order) return '<p>Pedido no válido.</p>';

        ob_start();
        ?>
        <h2><?php printf(__('RMA Request for Order #%d', 'dl-woo-rma'), $order_id); ?></h2>

        <form method="post">
            <p>
                <label><?php _e('Comment', 'dl-woo-rma'); ?></label><br>
                <textarea name="rma_comment" required></textarea>
            </p>
            <p>
                <label><?php _e('Available Action', 'dl-woo-rma'); ?></label><br>
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
            <p>
                <button type="submit" name="rma_submit" class="button"><?php _e('Generate RMA', 'dl-woo-rma'); ?></button>
            </p>
        </form>
        <?php

        return ob_get_clean();

    }
}
