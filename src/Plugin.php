<?php

namespace DL\RMA;

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
    }
}
