<?php

namespace DL\RMA;

defined('ABSPATH') || exit;

class Settings
{

    /**
     * Añadimos al menú enlace a la página de ajustes
     * @return void
     * @author Daniel Lucia
     */
    public function add_settings_page()
    {
        add_submenu_page(
            'woocommerce',
            __('RMA Settings', 'dl-woo-rma'),
            __('RMA', 'dl-woo-rma'),
            'manage_options',
            'dl-woo-rma-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Registramos ajustes
     * @return void
     * @author Daniel Lucia
     */
    public function register_settings()
    {
        register_setting('dl_woo_rma_group', 'dl_woo_rma_states');
        register_setting('dl_woo_rma_group', 'dl_woo_rma_rules');
    }

    /**
     * Mostramos página de ajustes
     * @return void
     * @author Daniel Lucia
     */
    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('RMA options', 'dl-woo-rma'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('dl_woo_rma_group'); ?>
                <?php do_settings_sections('dl_woo_rma_group'); ?>

                <h2><?php _e('RMA States', 'dl-woo-rma'); ?></h2>
                <textarea name="dl_woo_rma_states" rows="5" cols="50"><?php echo esc_textarea(get_option('dl_woo_rma_states', "En espera\nProcesado\nAprobado\nRechazado")); ?></textarea>
                <p class="description"><?php _e('Un estado por línea.', 'dl-woo-rma'); ?></p>

                <h2><?php _e('RMA Time Rules', 'dl-woo-rma'); ?></h2>
                <textarea name="dl_woo_rma_rules" rows="5" cols="50"><?php echo esc_textarea(get_option('dl_woo_rma_rules', "15|Devolución\n60|Cambio\n1095|Garantía")); ?></textarea>
                <p class="description"><?php _e('Format: days|action (one per line)', 'dl-woo-rma'); ?></p>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
