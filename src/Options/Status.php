<?php 

namespace DL\RMA\Options;

defined('ABSPATH') || exit;

class Status {

    private $default_statuses = ['Pending', 'Approved', 'Rejected', 'Received', 'Completed'];
    
    /**
     * Obtiene los estados desde la opción, o los estados por defecto si no existen.
     * @return array
     * @author Daniel Lucia
     */
    public function getStatuses(): array
    {
        $statuses = get_option('dl_woo_rma_states', []);

        if (!is_array($statuses)) {
            $statuses = array_filter(array_map('trim', explode("\n", $statuses)));
        }

        if (empty($statuses)) {
            $statuses = $this->default_statuses;
        }

        return $statuses;

    }
}