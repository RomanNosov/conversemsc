<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
// Добавление блоков
try {
    wa('site');
    $site_block_model = new siteBlockModel();
    // Форма для ввода купона
    $block_form = $site_block_model->getById('flexdiscount.form');
    if (!$block_form) {
        $file_form = dirname(__FILE__) . '/data/flexdiscount.form.html';
        if (file_exists($file_form)) {
            $block_content_form = file_get_contents($file_form);
            $site_block_model->add(array(
                "id" => "flexdiscount.form",
                "content" => $block_content_form,
                "description" => "Форма для ввода купона",
            ));
        }
    }
    // Примененные скидки
    $block_discounts = $site_block_model->getById('flexdiscount.discounts');
    if (!$block_discounts) {
        $file_discounts = dirname(__FILE__) . '/data/flexdiscount.discounts.html';
        if (file_exists($file_discounts)) {
            $block_content_discounts = file_get_contents($file_discounts);
            $site_block_model->add(array(
                "id" => "flexdiscount.discounts",
                "content" => $block_content_discounts,
                "description" => "Примененные скидки",
            ));
        }
    }
    // Доступные скидки
    $block_available = $site_block_model->getById('flexdiscount.available');
    if (!$block_available) {
        $file_available = dirname(__FILE__) . '/data/flexdiscount.available.html';
        if (file_exists($file_available)) {
            $block_content_available = file_get_contents($file_available);
            $site_block_model->add(array(
                "id" => "flexdiscount.available",
                "content" => $block_content_available,
                "description" => "Доступные скидки",
            ));
        }
    }
    // Действующие скидки
    $block_pd = $site_block_model->getById('flexdiscount.product.discounts');
    if (!$block_pd) {
        $file_pd = dirname(__FILE__) . '/data/flexdiscount.product.discounts.html';
        if (file_exists($file_pd)) {
            $block_content_pd = file_get_contents($file_pd);
            $site_block_model->add(array(
                "id" => "flexdiscount.product.discounts",
                "content" => $block_content_pd,
                "description" => "Действующие скидки для товара",
            ));
        }
    }
    // Начисленные бонусы
    $block_affiliate = $site_block_model->getById('flexdiscount.affiliate');
    if (!$block_affiliate) {
        $file_affiliate = dirname(__FILE__) . '/data/flexdiscount.affiliate.html';
        if (file_exists($file_affiliate)) {
            $block_content_affiliate = file_get_contents($file_affiliate);
            $site_block_model->add(array(
                "id" => "flexdiscount.affiliate",
                "content" => $block_content_affiliate,
                "description" => "Начисленные бонусы",
            ));
        }
    }
} catch (Exception $e) {
    
}