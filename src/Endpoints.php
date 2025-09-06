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
        $valid_order = apply_filters('dl_woo_rma_is_valid_order_for_rma', true, $order);

        if (!$valid_order) {
            return $actions;
        }
        
        $actions['rma'] = [
            'url'  => $url,
            'name' => __('Process RMA', 'dl-woo-rma')
        ];

        return $actions;
    }

}
