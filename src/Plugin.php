<?php

namespace DL\RMA;

use DL\RMA\Options\OrderStatus;

defined('ABSPATH') || exit;

class Plugin
{

    /**
     * Inicializamos el plugin
     * @return void
     * @author Daniel Lucia
     */
    public function init(): void
    {

        $cpt = new CPT();
        add_action('init', [$cpt, 'register']);

        $settings = new Settings();
        add_action('admin_menu', [$settings, 'add_settings_page']);
        add_action('admin_init', [$settings, 'register_settings']);

        $endpoints = new Endpoints();
        add_filter('woocommerce_my_account_my_orders_actions', [$endpoints, 'add_rma_link'], 10, 2);

        $form = new Form();
        add_action('woocommerce_account_content', [$form, 'render'], 1);

        $orderStatus = new OrderStatus();
        add_filter('dl_woo_rma_is_valid_order_for_rma', [$orderStatus, 'checkOrderStatusForRma'], 10, 2);

        // Encolar CSS del plugin
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }


    /**
     * Encolamos los estilos del plugin
     * @return void
     * @author Daniel Lucia
     */
    public function enqueue_styles(): void
    {
        wp_enqueue_style(
            'dl-woo-rma',
            plugins_url('../assets/css/dl-woo-rma.css', __FILE__),
            [],
            DL_WOO_RMA_VERSION
        );
    }
}
