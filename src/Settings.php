<?php

namespace DL\RMA;

use DL\RMA\Options\Status;

defined('ABSPATH') || exit;

class Settings
{
    private $statuses;

    public function __construct() {
        $status = new Status;
        $this->statuses = $status->getStatuses();
    }

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
        register_setting('dl_woo_rma_group', 'dl_woo_rma_states', [
            'type' => 'array',
            'sanitize_callback' => function($statuss) {
                if (!is_array($statuss)) return [];
                return array_values(array_filter(array_map('sanitize_text_field', $statuss)));
            }
        ]);
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
            <form method="post" action="options.php" id="dl-woo-rma-settings-form">
                <?php settings_fields('dl_woo_rma_group'); ?>
                <?php do_settings_sections('dl_woo_rma_group'); ?>

                <h2><?php _e('RMA States', 'dl-woo-rma'); ?></h2>
                <div id="dl-woo-rma-states-list">
                    <?php foreach ($this->statuses as $i => $status): ?>
                        <div class="dl-woo-rma-state-row" style="margin-bottom:6px;">
                            <input type="text" name="dl_woo_rma_states[]" value="<?php echo esc_attr($status); ?>" style="width:300px;" />
                            <button type="button" class="button dl-woo-rma-remove-state" title="<?php esc_attr_e('Eliminar', 'dl-woo-rma'); ?>">-</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button" id="dl-woo-rma-add-state"><?php _e('Añadir nuevo', 'dl-woo-rma'); ?></button>

                <h2><?php _e('RMA Time Rules', 'dl-woo-rma'); ?></h2>
                <textarea name="dl_woo_rma_rules" rows="5" cols="50"><?php echo esc_textarea(get_option('dl_woo_rma_rules', "15|Devolución\n60|Cambio\n1095|Garantía")); ?></textarea>
                <p class="description"><?php _e('Format: days|action (one per line)', 'dl-woo-rma'); ?></p>

                <?php submit_button(); ?>
            </form>
        </div>
        <script>
        jQuery(function($){
            $('#dl-woo-rma-add-state').on('click', function(e){
                e.preventDefault();
                $('#dl-woo-rma-states-list').append(
                    '<div class="dl-woo-rma-state-row" style="margin-bottom:6px;">' +
                    '<input type="text" name="dl_woo_rma_states[]" value="" style="width:300px;" /> ' +
                    '<button type="button" class="button dl-woo-rma-remove-state" title="<?php esc_attr_e('Eliminar', 'dl-woo-rma'); ?>">-</button>' +
                    '</div>'
                );
            });
            $(document).on('click', '.dl-woo-rma-remove-state', function(){
                $(this).closest('.dl-woo-rma-state-row').remove();
            });
        });
        </script>
        <?php
    }
}
