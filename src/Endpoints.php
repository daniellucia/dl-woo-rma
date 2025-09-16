<?php

namespace DL\RMA;

defined('ABSPATH') || exit;

class Endpoints
{

    private $rma;
    public function __construct()
    {
        $this->rma = new RMA();
        add_filter('dl_woo_rma_is_valid_order_for_rma', [$this, 'check_order'], 10, 3);
    }

    /**
     * Añade un enlace "Tramitar RMA" en la página de pedidos
     * @param mixed $actions
     * @param mixed $order
     * @author Daniel Lucia
     */
    public function add_rma_link($actions, $order)
    {
        $order_id = $order->get_id();
        $customer_id = get_current_user_id();
        $url = add_query_arg(['rma' => $order_id], wc_get_account_endpoint_url('orders'));
        $valid_order = apply_filters('dl_woo_rma_is_valid_order_for_rma', true, $order, $customer_id);
        
        if (!$valid_order) {
            return $actions;
        } 
        
        $actions['rma'] = [
            'url'  => $url,
            'name' => __('Process RMA', 'dl-woo-rma')
        ];

        return $actions;
    }

    /**
     * Comprueba si el pedido es válido para RMA
     * @param bool $valid
     * @param \WC_Order $order
     * @return bool
     * @author Daniel Lucia
     */
    public function check_order($valid, $order, $customer_id) {

        //Comprobamos si ya existe una RMA para este pedido
        if ($this->rma->exists($order->get_id(), $customer_id)) {
            return false;
        }

        return $valid;
    }

}
