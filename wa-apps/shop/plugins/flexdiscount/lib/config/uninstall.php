<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
// Удаление созданных блоков
wa('site');
$ids = array("flexdiscount.form", "flexdiscount.available", "flexdiscount.discounts", "flexdiscount.affiliate", "flexdiscount.product.discounts");
$model = new siteBlockModel();
foreach ($ids as $id) {
    $block = $model->getById($id);
    if ($block) {
        $model->deleteById($id);
    }
}