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

    /**
     * Añade los metaboxes personalizados para el CPT de RMA
     * @return void
     * @author Daniel Lucia
     */
    public function add_rma_meta_boxes()
    {
        add_meta_box(
            'rma_custom_fields',
            __('Datos de RMA', 'dl-woo-rma'),
            [$this, 'render_rma_meta_box'],
            'rma',
            'normal',
            'default'
        );
    }

    /**
     * Renderiza los campos personalizados en el metabox
     * @param mixed $post
     * @return void
     * @author Daniel Lucia
     */
    public function render_rma_meta_box($post)
    {

        wp_nonce_field('rma_custom_fields_nonce', 'rma_custom_fields_nonce');

        $order_id    = get_post_meta($post->ID, '_rma_order_id', true);
        $customer_id = get_post_meta($post->ID, '_rma_customer_id', true);
        $product_id  = get_post_meta($post->ID, '_rma_product_id', true);
        $reason      = get_post_meta($post->ID, '_rma_reason', true);
        $comments    = get_post_meta($post->ID, '_rma_comments', true);
?>
        <p>
            <label for="rma_order_id"><strong><?php _e('Order ID', 'dl-woo-rma'); ?>:</strong></label><br>
            <input type="number" name="rma_order_id" id="rma_order_id" value="<?php echo esc_attr($order_id); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="rma_customer_id"><strong><?php _e('Customer ID', 'dl-woo-rma'); ?>:</strong></label><br>
            <input type="number" name="rma_customer_id" id="rma_customer_id" value="<?php echo esc_attr($customer_id); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="rma_product_id"><strong><?php _e('Product ID', 'dl-woo-rma'); ?>:</strong></label><br>
            <input type="number" name="rma_product_id" id="rma_product_id" value="<?php echo esc_attr($product_id); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="rma_reason"><strong><?php _e('Reason', 'dl-woo-rma'); ?>:</strong></label><br>
            <input type="text" name="rma_reason" id="rma_reason" value="<?php echo esc_attr($reason); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="rma_comments"><strong><?php _e('Comments', 'dl-woo-rma'); ?>:</strong></label><br>
            <textarea name="rma_comments" id="rma_comments" style="width:100%;"><?php echo esc_textarea($comments); ?></textarea>
        </p>
<?php
    }

    /**
     * Guardamos los campos personalizados del metabox
     * @param mixed $post_id
     * @return void
     * @author Daniel Lucia
     */
    public function save_rma_meta_boxes($post_id)
    {
        if (!isset($_POST['rma_custom_fields_nonce']) || !wp_verify_nonce($_POST['rma_custom_fields_nonce'], 'rma_custom_fields_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['rma_order_id'])) {
            update_post_meta($post_id, '_rma_order_id', intval($_POST['rma_order_id']));
        }

        if (isset($_POST['rma_customer_id'])) {
            update_post_meta($post_id, '_rma_customer_id', intval($_POST['rma_customer_id']));
        }

        if (isset($_POST['rma_product_id'])) {
            update_post_meta($post_id, '_rma_product_id', intval($_POST['rma_product_id']));
        }

        if (isset($_POST['rma_reason'])) {
            update_post_meta($post_id, '_rma_reason', sanitize_text_field($_POST['rma_reason']));
        }

        if (isset($_POST['rma_comments'])) {
            update_post_meta($post_id, '_rma_comments', sanitize_textarea_field($_POST['rma_comments']));
        }
    }

    /**
     * Añadimos columnas
     * @param mixed $columns
     * @author Daniel Lucia
     */
    public function add_rma_columns($columns)
    {

        unset($columns['date']);

        $columns['return_id']   = __('Return ID', 'dl-woo-rma');
        $columns['order_id']   = __('Order ID', 'dl-woo-rma');
        $columns['product']    = __('Product', 'dl-woo-rma');
        $columns['customer']   = __('Customer', 'dl-woo-rma');
        $columns['status']     = __('Status', 'dl-woo-rma');
        return $columns;
    }

    /**
     * Añadimos contenido a las columnas personalizadas
     * @param mixed $column
     * @param mixed $post_id
     * @return void
     * @author Daniel Lucia
     */
    public function render_rma_columns($column, $post_id)
    {
        switch ($column) {
            case 'order_id':

                echo esc_html(get_post_meta($post_id, '_rma_order_id', true));
                break;
            case 'return_id':

                echo esc_html(get_post_meta($post_id, '_rma_return_id', true));
                break;

            case 'product':

                $product_id = get_post_meta($post_id, '_rma_product_id', true);
                if ($product_id) {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        echo esc_html($product->get_name());
                    } else {
                        echo esc_html($product_id);
                    }
                }
                break;

            case 'customer':

                $customer_id = get_post_meta($post_id, '_rma_customer_id', true);
                if ($customer_id) {
                    $user = get_userdata($customer_id);
                    if ($user) {
                        echo esc_html($user->first_name . ' ' . $user->last_name);
                    } else {
                        echo esc_html($customer_id);
                    }
                }
                break;

            case 'status':

                echo esc_html(get_post_meta($post_id, 'status', true));
                break;

            case 'date':
                $post = get_post($post_id);
                if ($post) {
                    echo esc_html(get_the_date('Y-m-d H:i', $post));
                }
                break;
        }
    }
}
