<?php

/**
 * Plugin Name: RMA for WooCommerce
 * Description: RMA Management for WooCommerce
 * Version: 0.0.1
 * Author: Daniel Lúcia
 * Author URI: http://www.daniellucia.es
 * textdomain: dl-woo-rma
 * Requires Plugins: woocommerce
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/*
Copyright (C) 2025  Daniel Lucia (https://daniellucia.es)

Este programa es software libre: puedes redistribuirlo y/o modificarlo
bajo los términos de la Licencia Pública General GNU publicada por
la Free Software Foundation, ya sea la versión 2 de la Licencia,
o (a tu elección) cualquier versión posterior.

Este programa se distribuye con la esperanza de que sea útil,
pero SIN NINGUNA GARANTÍA; ni siquiera la garantía implícita de
COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO PARTICULAR.
Consulta la Licencia Pública General GNU para más detalles.

Deberías haber recibido una copia de la Licencia Pública General GNU
junto con este programa. En caso contrario, consulta <https://www.gnu.org/licenses/gpl-2.0.html>.
*/

use DL\RMA\CPT;
use DL\RMA\Plugin;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

define('DL_WOO_RMA_VERSION', '0.0.1');
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
    $plugin = new Plugin();
    $plugin->init();
});

/**
 * Limpiamos caché al activar o desactivar el plugin
 */
register_activation_hook(__FILE__, function() {
    wp_cache_flush();
});

register_deactivation_hook(__FILE__, function() {
    wp_cache_flush();
});