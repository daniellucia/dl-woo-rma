<?php defined('ABSPATH') || exit; ?>
<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
    <thead>
        <tr>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?= __('Order ID', 'dl-woo-rma') ?></span></th>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr"><?= __('Product', 'dl-woo-rma') ?></span></th>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?= __('Status', 'dl-woo-rma') ?></span></th>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?= __('Date', 'dl-woo-rma') ?></span></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($rmas as $rma): ?>
            <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
                <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" scope="row">
                    <?= $this->e($rma->order_id) ?>
                </th>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
                    <?php if ($rma->product): ?>
                        <?= $this->e($rma->product->name) ?>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" dta-title="<?= __('Status', 'dl-woo-rma') ?>">
                    <?= $this->e($rma->status) ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">
                    <?= $this->e($rma->date_created) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>