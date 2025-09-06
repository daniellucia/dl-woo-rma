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
        return wc_get_order_statuses();
    }
}
