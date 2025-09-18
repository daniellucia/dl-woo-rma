<?php defined('ABSPATH') || exit; ?>
<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
    <thead>
        <tr>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?= __('Order ID', 'dl-ticket-manager') ?></span></th>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr"><?= __('Product', 'dl-ticket-manager') ?></span></th>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?= __('Status', 'dl-ticket-manager') ?></span></th>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?= __('Date', 'dl-ticket-manager') ?></span></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($tickets as $ticket): ?>
            <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
                <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" scope="row">
                    <?= $this->e($ticket['order_id']) ?>
                </th>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
                    <?= $this->e($ticket['product']) ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" dta-title="Estado">
                    <?= $this->e($ticket['status']) ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">
                    <?= $this->e($ticket['date']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>