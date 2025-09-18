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
        add_action('add_meta_boxes', [$cpt, 'add_rma_meta_boxes']);
        add_action('save_post_rma', [$cpt, 'save_rma_meta_boxes']);

        $settings = new Settings();
        add_action('admin_menu', [$settings, 'add_settings_page']);
        add_action('admin_init', [$settings, 'register_settings']);
        add_action('template_redirect', [$settings, 'maybe_hide_orders_list']);

        $endpoints = new Endpoints();
        add_filter('woocommerce_my_account_my_orders_actions', [$endpoints, 'add_rma_link'], 10, 2);
        add_filter('dl_woo_rma_is_valid_order_for_rma', [$endpoints, 'check_order'], 10, 3);
        add_filter('woocommerce_account_orders_columns', [$endpoints, 'add_account_orders_columns'], 20, 1);
        add_action('woocommerce_my_account_my_orders_column_rma-status', [$endpoints, 'rma_status_column'], 10, 1);

        $form = new Form();
        add_action('woocommerce_account_content', [$form, 'render'], 20);

        $orderStatus = new OrderStatus();
        add_filter('dl_woo_rma_is_valid_order_for_rma', [$orderStatus, 'checkOrderStatusForRma'], 10, 2);

        $account = new Account();
        add_action('init', [$account, 'add_rma_endpoint']);
        add_filter('woocommerce_account_menu_items', [$account, 'add_account_menu_item']);
        add_action('woocommerce_account_rma_endpoint', [$account, 'rma_content']);

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
