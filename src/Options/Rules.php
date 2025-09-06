<?php

namespace DL\RMA\Options;

defined('ABSPATH') || exit;

class Rules
{

    /**
     * Obtiene las reglas
     * @return array
     * @author Daniel Lucia
     */
    public function getRules(): array
    {
        $rules_raw = get_option('dl_woo_rma_rules', "15|Devolución\n60|Cambio\n1095|Garantía");
        $rules = [];
        
        foreach (explode("\n", $rules_raw) as $rule) {
            $parts = array_map('trim', explode('|', $rule, 2));
            if (count($parts) === 2) {
                $rules[] = $parts;
            }
        }

        if (empty($rules)) {
            $rules = [['', '']];
        }

        return $rules;
    }
}
