<?php

namespace DL\RMA;

use DL\RMA\Options\Status;
use DL\RMA\Options\Rules;

defined('ABSPATH') || exit;

class Settings
{
    private $statuses;
    private $rules;

    public function __construct() {
        $status = new Status;
        $this->statuses = $status->getStatuses();

        $rules = new Rules;
        $this->rules = $rules->getRules();
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
            'sanitize_callback' => function($statuses) {
                if (!is_array($statuses)) {
                    return [];
                }
                return array_values(array_filter(array_map('sanitize_text_field', $statuses)));
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
                            <button type="button" class="button dl-woo-rma-remove-state" title="<?php esc_attr_e('Delete', 'dl-woo-rma'); ?>">-</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button" id="dl-woo-rma-add-state"><?php _e('Add new', 'dl-woo-rma'); ?></button>

                <h2><?php _e('RMA Time Rules', 'dl-woo-rma'); ?></h2>
                <div id="dl-woo-rma-rules-list">
                    <?php foreach ($this->rules as $rule): ?>
                        <div class="dl-woo-rma-rule-row" style="margin-bottom:6px;">
                            <input type="number" min="0" name="dl_woo_rma_rules_days[]" value="<?php echo esc_attr($rule[0]); ?>" style="width:80px;" placeholder="<?php esc_attr_e('Days', 'dl-woo-rma'); ?>" />
                            <input type="text" name="dl_woo_rma_rules_action[]" value="<?php echo esc_attr($rule[1]); ?>" style="width:200px;" placeholder="<?php esc_attr_e('Action', 'dl-woo-rma'); ?>" />
                            <button type="button" class="button dl-woo-rma-remove-rule" title="<?php esc_attr_e('Delete', 'dl-woo-rma'); ?>">-</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button" id="dl-woo-rma-add-rule"><?php _e('Add new', 'dl-woo-rma'); ?></button>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <script>
        jQuery(function($){

            //Estados
            $('#dl-woo-rma-add-state').on('click', function(e){
                e.preventDefault();
                $('#dl-woo-rma-states-list').append(
                    '<div class="dl-woo-rma-state-row" style="margin-bottom:6px;">' +
                    '<input type="text" name="dl_woo_rma_states[]" value="" style="width:300px;" /> ' +
                    '<button type="button" class="button dl-woo-rma-remove-state" title="<?php esc_attr_e('Delete', 'dl-woo-rma'); ?>">-</button>' +
                    '</div>'
                );
            });

            $(document).on('click', '.dl-woo-rma-remove-state', function(){
                $(this).closest('.dl-woo-rma-state-row').remove();
            });

            // Reglas
            $('#dl-woo-rma-add-rule').on('click', function(e){
                e.preventDefault();
                $('#dl-woo-rma-rules-list').append(
                    '<div class="dl-woo-rma-rule-row" style="margin-bottom:6px;">' +
                    '<input type="number" min="0" name="dl_woo_rma_rules_days[]" value="" style="width:80px;" placeholder="<?php esc_attr_e('Días', 'dl-woo-rma'); ?>" /> ' +
                    '<input type="text" name="dl_woo_rma_rules_action[]" value="" style="width:200px;" placeholder="<?php esc_attr_e('Acción', 'dl-woo-rma'); ?>" /> ' +
                    '<button type="button" class="button dl-woo-rma-remove-rule" title="<?php esc_attr_e('Delete', 'dl-woo-rma'); ?>">-</button>' +
                    '</div>'
                );
            });

            $(document).on('click', '.dl-woo-rma-remove-rule', function(){
                $(this).closest('.dl-woo-rma-rule-row').remove();
            });

            $('#dl-woo-rma-settings-form').on('submit', function(){
                var rules = [];
                $('#dl-woo-rma-rules-list .dl-woo-rma-rule-row').each(function(){
                    var days = $(this).find('input[name="dl_woo_rma_rules_days[]"]').val();
                    var action = $(this).find('input[name="dl_woo_rma_rules_action[]"]').val();
                    if(days && action){
                        rules.push(days + '|' + action);
                    }
                });
                
                if ($('#dl_woo_rma_rules_hidden').length) {
                    $('#dl_woo_rma_rules_hidden').val(rules.join("\n"));
                } else {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'dl_woo_rma_rules_hidden',
                        name: 'dl_woo_rma_rules',
                        value: rules.join("\n")
                    }).appendTo('#dl-woo-rma-settings-form');
                }
                
                $('textarea[name="dl_woo_rma_rules"]').prop('disabled', true);
            });

        });
        </script>
        <?php
    }
}
