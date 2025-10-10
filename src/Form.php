<?php

namespace DL\RMA;

use League\Plates\Engine;

defined('ABSPATH') || exit;

class Form
{
    /**
     * Renderiza el formulario de RMA en pasos
     * @param array $atts
     * @return string
     * @author Daniel Lucia
     */
    public function render($atts)
    {
        if (!isset($_GET['rma'])) {
            return '';
        }

        $order_id = intval($_GET['rma']);
        if ($order_id <= 0) {
            return '';
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return '<p>' . __('Invalid order.', 'dl-woo-rma') . '</p>';
        }

        // Paso actual
        $step = isset($_GET['rma_step']) ? intval($_GET['rma_step']) : 1;

        echo '<h2>' . sprintf(__('RMA Request for Order #%d', 'dl-woo-rma'), $order_id) . '</h2>';

        do_action('dl_woo_rma_before_form', $order, $step);
        
        if ($step === 1) {

            echo $this->render_product_selection_step($order);
        } elseif ($step === 2 && !empty($_GET['rma_products'])) {

            if(!$this->validate_products($_GET['rma_products'], $order)) {
                return '<p style="color:red;">' . __('Invalid product selection. Please try again.', 'dl-woo-rma') . '</p>';
            }

            if (!$this->validate_nonce($_GET['dl_woo_rma_nonce_step1'], 'dl_woo_rma_step1')) {
                return '<p style="color:red;">' . __('Security check failed. Please try again.', 'dl-woo-rma') . '</p>';
            }

            $selected_products = array_map('esc_attr', (array)$_GET['rma_products']);
            echo $this->render_action_step($order, $selected_products);
        } elseif ($step === 3 && isset($_GET['rma_submit'])) {

            if (!$this->validate_nonce($_GET['dl_woo_rma_nonce_step3'], 'dl_woo_rma_step3')) {
                return '<p style="color:red;">' . __('Security check failed. Please try again.', 'dl-woo-rma') . '</p>';
            }

            $rma_ids = $this->create_rmas($order);

            if (!empty($rma_ids)) {
                echo '<p>' . sprintf(__('RMA requests created successfully: %s', 'dl-woo-rma'), implode(', ', $rma_ids)) . '</p>';
            }

        } else {

            echo $this->render_product_selection_step($order, true);
        }

        return '';
    }

    /**
     * Valida que los productos seleccionados pertenezcan al pedido
     * @param mixed $selected_products
     * @param mixed $order
     * @return bool
     * @author Daniel Lucia
     */
    private function validate_products($selected_products, $order): bool {
        $valid_product_ids = [];
        foreach ($order->get_items() as $item_id => $item) {
            $valid_product_ids[] = strval($item_id);
            $data = ['id' => intval($item->get_product_id()), 'i' => intval($item->get_quantity())];
            $encoded = base64_encode(json_encode($data));
            $valid_product_ids[] = $encoded;
        }

        foreach ($selected_products as $item) {
            if (!in_array($item, $valid_product_ids, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Crea los RMA para los productos seleccionados
     * @param mixed $order
     * @return int[]
     * @author Daniel Lucia
     */
    private function create_rmas($order):array {
            $rma = new RMA();
            $selected_products = $this->process_selected_products($_GET['rma_products']);
            $order_id = $order->get_id();
            $customer_id = $order->get_customer_id();
            $reason = isset($_GET['rma_action']) ? sanitize_text_field($_GET['rma_action']) : '';
            $comments = isset($_GET['rma_comment']) ? sanitize_textarea_field($_GET['rma_comment']) : '';

            $rma_ids = [];
            foreach ($selected_products as $item) {
                $product_id = is_array($item) && isset($item['id']) ? intval($item['id']) : 0;
                $index = is_array($item) && isset($item['i']) ? intval($item['i']) : 0;

                $rma_id = $rma->create($order_id, $customer_id, $product_id, $index,  $reason, $comments);
                $rma_ids[] = $rma_id;
            }

            return $rma_ids;
    }

    /**
     * Paso 1: Selección de productos
     * @param mixed $order
     * @param mixed $error
     * @return bool|string
     * @author Daniel Lucia
     */
    private function render_product_selection_step($order, $error = false)
    {
        $template_folder = DL_WOO_RMA_PATH . 'src/Views/';
        $template = new Engine($template_folder);

        $rma = new RMA();
        $rmas = $rma->loadByOrderId($order->get_id(), get_current_user_id());

        return $template->render('step-product-selection', [
            'order' => $order,
            'error' => $error,
            'rmas' => $rmas,
            'rma' => $rma
        ]);
    }

    /**
     * Paso 2: Acción y comentarios
     * @param mixed $order
     * @param mixed $selected_products
     * @return bool|string
     * @author Daniel Lucia
     */
    private function render_action_step($order, $selected_products)
    {
        $selected_products = apply_filters('dl_woo_rma_validate_selected_products', $selected_products, $order);

        $template_folder = DL_WOO_RMA_PATH . 'src/Views/';
        $template = new Engine($template_folder);

        return $template->render('step-action', [
            'order' => $order,
            'selected_products' => $selected_products
        ]);
    }

    /**
     * Procesa los productos seleccionados desde el formulario
     * @param mixed $selected_products
     * @return int[]
     * @author Daniel Lucia
     */
    private function process_selected_products($selected_products)
    {
        $processed = [];
        foreach ($selected_products as $item) {

            // Si es un número, lo añadimos directamente
            if (is_numeric($item)) {
                $processed[] = intval($item);
                continue;
            }

            // Si es un string, lo decodificamos
            $item = base64_decode($item);
            $data = json_decode($item, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($data)) {
                $processed[] = $data;
            }
        }

        return $processed;
    }

    /**
     * Valida el nonce para seguridad
     * @param mixed $nonce
     * @param mixed $action
     * @return bool
     * @author Daniel Lucia
     */
    private function validate_nonce($nonce, $action)
    {
        if (!isset($nonce) || !wp_verify_nonce($nonce, $action)) {
            return false;
        }
        return true;
    }
}
