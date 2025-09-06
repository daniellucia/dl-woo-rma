<?php

namespace DL\RMA\Options;

defined('ABSPATH') || exit;

class OrderStatus
{

    /**
     * Obtiene los estados de pedido válidos para RMA
     * @return array
     * @author Daniel Lucia
     */
    public function getValidOrderStatuses(): array
    {
        return get_option('dl_woo_rma_valid_order_statuses', []);
    }

    /**
     * Obtiene todos los estados de pedido de WooCommerce
     * @return array
     * @author Daniel Lucia
     */
    public function getAllOrderStatuses(): array
    {
        $statuses =  wc_get_order_statuses();

        //quitamos prefijo wc- a los estados
        return array_combine(array_map(function($key) {
            return str_replace('wc-', '', $key);
        }, array_keys($statuses)), array_values($statuses));
    }

    /**
     * Filtro para comprobar si un pedido es valido para RMA según su estado
     * @param mixed $valid
     * @param mixed $order
     * @return bool
     * @author Daniel Lucia
     */
    public function checkOrderStatusForRma($valid, $order): bool
    {
        //Si no tenemos estados válidos, permitimos todos
        $valid_statuses = $this->getValidOrderStatuses();
        if (empty($valid_statuses)) {
            return true;
        }

        $status = $order->get_status();
        
        return in_array($status, $valid_statuses);
    }
}
