<?php

/**
 * Plugin Name: RMA for WooCommerce
 * Description: RMA Management for WooCommerce
 * Version: 0.0.1
 * Author: Daniel Lúcia
 * Author URI: http://www.daniellucia.es
 * textdomain: dl-woo-rma
 * Requires Plugins: WooCommerce
 */

use DL\RMA\CPT;
use DL\RMA\Plugin;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

define('DL_WOO_RMA_PATH', plugin_dir_path(__FILE__));
define('DL_WOO_RMA_URL', plugin_dir_url(__FILE__));

// Hooks de activación
register_activation_hook(__FILE__, function () {
    (new CPT())->register();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

// Iniciamos el plugin
add_action('plugins_loaded', function () {

    load_plugin_textdomain('dl-woo-rma', false, dirname(plugin_basename(__FILE__)) . '/languages');

    (new Plugin())->init();
});
