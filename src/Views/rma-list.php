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
        <?php foreach ($rmas as $rma): ?>
            <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
                <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" scope="row">
                    <?= $this->e($rma->order_id) ?>
                </th>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
                    <?= $this->e($rma->product_id) ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" dta-title="Estado">
                    <?= $this->e($rma->status) ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">
                    <?= $this->e($rma->date_created) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>