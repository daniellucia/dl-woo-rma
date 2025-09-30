<?php

namespace DL\RMA;

defined('ABSPATH') || exit;

class RMA
{

    private $id_rma;
    private $statuses = ['pending', 'approved', 'rejected', 'completed'];

    public $order_id, $customer_id, $product_id, $reason, $comments, $status, $product, $date_created;


    public function __construct($id_rma = 0)
    {
        $this->id_rma = $id_rma;
        $this->statuses = apply_filters('dl_woo_rma_statuses', $this->statuses);

        if ($this->id_rma > 0) {
            $this->load();
        }
    }

    /**
     * Comprueba si un usuario tiene solicitudes de RMA
     * @param int $customer_id
     * @return bool
     * @author Daniel Lucia
     */
    public function userHasRma(int $customer_id = 0): bool
    {

        if ($customer_id <= 0) {
            $customer_id = get_current_user_id();
        }

        $rmas = $this->loadByCustomerId($customer_id);

        return !empty($rmas);
    }

    /**
     * Carga los datos de la RMA desde la base de datos
     * @param int $id_rma
     * @return void
     * @author Daniel Lucia
     */
    public function load(int $id_rma = 0): void
    {
        if ($id_rma > 0) {
            $this->id_rma = $id_rma;
        }

        $post = get_post($this->id_rma);

        if ($post && $post->post_type === 'rma') {
            $this->order_id = get_post_meta($this->id_rma, '_rma_order_id', true);
            $this->customer_id = get_post_meta($this->id_rma, '_rma_customer_id', true);
            $this->product_id = get_post_meta($this->id_rma, '_rma_product_id', true);
            $this->reason = get_post_meta($this->id_rma, '_rma_reason', true);
            $this->comments = get_post_meta($this->id_rma, '_rma_comments', true);
            $this->status = ucfirst(get_post_meta($this->id_rma, 'status', true));
            $this->date_created = $post->post_date;

            //Cargamos el producto
            if ($this->product_id > 0) {
                $this->product = wc_get_product($this->product_id);
            }
        }
    }

    /**
     * Obtenemos las solicitudes de RMA por ID de cliente
     * @param int $customer_id
     * @return RMA[]
     * @author Daniel Lucia
     */
    public function loadByCustomerId(int $customer_id): array
    {
        $rmas = [];

        $args = [
            'post_type'   => 'rma',
            'post_status' => 'any',
            'fields'      => 'ids',
            'numberposts' => -1,
            'meta_query'  => [
                [
                    'key'     => '_rma_customer_id',
                    'value'   => $customer_id,
                    'compare' => '=',
                ],
            ],
        ];

        $posts = get_posts($args);

        return $this->load_posts($posts);
    }

    /**
     * Carga las solicitudes de RMA por ID de pedido
     * @param int $order_id
     * @param int $customer_id
     * @return RMA[]
     * @author Daniel Lucia
     */
    public function loadByOrderId(int $order_id, int $customer_id = 0): array
    {
        $rmas = [];

        $args = [
            'post_type'   => 'rma',
            'post_status' => 'any',
            'fields'      => 'ids',
            'numberposts' => 1,
            'meta_query'  => [
                [
                    'key'     => '_rma_order_id',
                    'value'   => $order_id,
                    'compare' => '=',
                ],
            ],
        ];

        if ($customer_id > 0) {
            $args['meta_query'][] = [
                'key'     => '_rma_customer_id',
                'value'   => $customer_id,
                'compare' => '=',
            ];
        }

        $posts = get_posts($args);

        return $this->load_posts($posts);
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
    public function create(int $order_id, int $customer_id, int $product_id, string $reason, string $comments): int
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
                '_rma_return_id'    => strtoupper(wp_generate_password(8, false, false)),
                'status'           => 'pending',
            ],
        ];

        $this->id_rma = wp_insert_post($rma_data);

        if (is_wp_error($this->id_rma)) {
            return 0;
        }

        do_action('dl_woo_rma_after_create', $this->id_rma);

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
    public function exists(int $order_id = 0, int $customer_id = 0, int $product_id = 0): bool
    {

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

    /**
     * Carga las solicitudes de RMA desde un array
     * @param mixed $posts
     * @return array
     * @author Daniel Lucia
     */
    private function load_posts($posts)
    {
        $rmas = [];
        foreach ($posts as $post_id) {
            $rma = apply_filters('dl_woo_rma_load', new RMA($post_id));
            $rmas[] = $rma;
        }
        return $rmas;
    }

    /**
     * Calcula el porcentaje de productos con RMA en un pedido
     *
     * @param integer $order_id
     * @return float
     */
    public function calculatePercentProductRMA(int $order_id): float {
        $order = wc_get_order($order_id);
        if (!$order) {
            return 0;
        }

        //Obtenemos el total de productos en el pedido
        $total_items = 0;
        foreach ($order->get_items() as $item) {
            $total_items += $item->get_quantity();
        }

        if ($total_items === 0) {
            return 0;
        }

        //Obtenemos el total de productos con RMA en el pedido
        $args = [
            'post_type'   => 'rma',
            'post_status' => 'any',
            'fields'      => 'ids',
            'numberposts' => -1,
            'meta_query'  => [
                [
                    'key'     => '_rma_order_id',
                    'value'   => $order_id,
                    'compare' => '=',
                ],
            ],
        ];

        $rma_posts = get_posts($args);
        $rma_count = count($rma_posts);

        return ($rma_count / $total_items) * 100;

    }
}
