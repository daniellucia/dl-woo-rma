<?php

namespace DL\RMA;

defined('ABSPATH') || exit;

class CPT
{

    /**
     * Registramos el Custom Post Type
     * @return void
     * @author Daniel Lucia
     */
    public function register()
    {
        register_post_type('rma', [
            'labels' => [
                'name' => __('RMA', 'dl-woo-rma'),
                'singular_name' => __('RMA', 'dl-woo-rma'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-randomize',
            'supports' => ['title', 'editor', 'custom-fields'],
        ]);
    }
}
