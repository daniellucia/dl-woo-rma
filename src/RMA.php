<?php

namespace DL\RMA;

defined('ABSPATH') || exit;

class RMA
{

    private $id_rma;
    private $statuses = ['pending', 'approved', 'rejected', 'completed'];

    public function __construct($id_rma = 0)
    {
        $this->id_rma = $id_rma;
        $this->statuses = apply_filters('dl_woo_rma_statuses', $this->statuses);
    }

    /**
     * Crear una nueva solicitud de RMA
     * @param int $order_id
     * @param int $customer_id
     * @param int $product_id
     * @param string $reason
     * @param string $comments
     * @return int ID de la solicitud de RMA creada o 0 si ya existe
     * @author Daniel Lucia
     */
    public function create(int $order_id, int $customer_id, int $product_id, string $reason, string $comments):int
    {

        if ($this->exists($order_id, $product_id, $customer_id)) {
            return 0; // Ya existe una solicitud de RMA para este pedido, producto y cliente
        }

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

    /**
     * Verifica si ya existe una solicitud de RMA para el mismo pedido, producto y cliente
     * @param int $order_id
     * @param int $customer_id
     * @param int $product_id
     * @return bool
     * @author Daniel Lucia
     */
    public function exists(int $order_id = 0, int $customer_id = 0, int $product_id = 0): bool {

        if ($order_id <= 0) {
            return false;
        }

        $args = [
            'post_type'   => 'rma',
            'post_status' => 'any',
            'fields'      => 'ids',
            'numberposts' => 1,
        ];

        $meta_query = [];
        $meta_query[] = [
            'key'     => '_rma_order_id',
            'value'   => $order_id,
            'compare' => '=',
        ];

        if ($product_id > 0) {
            $meta_query[] = [
                'key'     => '_rma_product_id',
                'value'   => $product_id,
                'compare' => '=',
            ];
        }

        if ($customer_id > 0) {
            $meta_query[] = [
                'key'     => '_rma_customer_id',
                'value'   => $customer_id,
                'compare' => '=',
            ];
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        $existing_rmas = get_posts($args);

        return !empty($existing_rmas);
    }

    /**
     * Obtiene los estados de las RMAs
     * @return array
     * @author Daniel Lucia
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }
}
