<?php

namespace DL\RMA;

defined('ABSPATH') || exit;

class RMA
{

    private $id_rma;

    public function __construct($id_rma = 0)
    {
        $this->id_rma = $id_rma;
    }

    /**
     * Crear una nueva solicitud de RMA
     * @param mixed $order_id
     * @param mixed $customer_id
     * @param mixed $products
     * @param mixed $reason
     * @param mixed $comments
     * @author Daniel Lucia
     */
    public function create($order_id, $customer_id, $product_id, $reason, $comments)
    {
        $rma_data = [
            'post_title'   => __('RMA for Order #', 'dl-woo-rma') . ' ' . $order_id,
            'post_content' => $comments,
            'post_status'  => 'publish',
            'post_type'    => 'rma',
            'meta_input'   => [
                '_rma_order_id'    => intval($order_id),
                '_rma_customer_id' => intval($customer_id),
                '_rma_product_id'  => intval($product_id),
                '_rma_reason'      => sanitize_text_field($reason),
                '_rma_comments'    => sanitize_textarea_field($comments),
                'status'           => 'pending',
            ],
        ];

        $this->id_rma = wp_insert_post($rma_data);

        if (is_wp_error($this->id_rma)) {
            return 0;
        }

        return $this->id_rma;
    }
}
