<?php

namespace DL\RMA;

use League\Plates\Engine;

defined('ABSPATH') || exit;

class Account
{

    private $rma;
    public function __construct()
    {
        $this->rma = new RMA();
    }

    /**
     * Añadimos el enlace "Devoluciones" en el menú de la cuenta
     * @param mixed $items
     * @author Daniel Lucia
     */
    public function add_account_menu_item($items)
    {
        //Si el usuario no tiene rma, no añadimos el enlace
        if (!$this->rma->userHasRma()) {
            return $items;
        }

        // Añade el enlace antes de "Cerrar sesión"
        $logout_key = array_search('customer-logout', array_keys($items));
        if ($logout_key !== false) {
            $items = array_slice($items, 0, $logout_key, true)
                + ['rma' => __('Returns', 'dl-woo-rma')]
                + array_slice($items, $logout_key, null, true);
        } else {
            $items['rma'] = __('Returns', 'dl-woo-rma');
        }
        return $items;
    }

    /**
     * Añade el endpoint "rma" para mostrar las RMAs del usuario
     * @return void
     * @author Daniel Lucia
     */
    public function add_rma_endpoint()
    {
        add_rewrite_endpoint('rma', EP_ROOT | EP_PAGES);
    }

    /**
     * Muestra el contenido de la página de devoluciones
     * @return void
     * @author Daniel Lucia
     */
    public function rma_content()
    {
        $customer_id = get_current_user_id();
        $rmas = $this->rma->loadByCustomerId($customer_id);

        $template_folder = DL_WOO_RMA_PATH . 'src/Views/';
        $template = new Engine($template_folder);

        echo $template->render('rma-list', [
            'rmas' => $rmas
        ]);
    }
}
