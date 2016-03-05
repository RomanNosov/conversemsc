<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
// Увеличиваем длину поля для названия скидки
try {
    $model->exec("ALTER TABLE shop_flexdiscount CHANGE `name` `name` VARCHAR (200) NOT NULL DEFAULT ''");
} catch (waDbException $e) {
    
}

try {
    $model->exec("SELECT code FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD code VARCHAR (50) NOT NULL DEFAULT ''");
}

try {
    wa('site');
    $site_block_model = new siteBlockModel();
    // Доступные скидки
    $block_available = $site_block_model->getById('flexdiscount.available');
    if (!$block_available) {
        $file_available = dirname(__FILE__) . '/../../config/data/flexdiscount.available.html';
        if (file_exists($file_available)) {
            $block_content_available = file_get_contents($file_available);
            $site_block_model->add(array(
                "id" => "flexdiscount.available",
                "content" => $block_content_available,
                "description" => "Доступные скидки",
            ));
        }
    }
} catch (Exception $e) {
    
}

try {
    // Действующие скидки
    $block_pd = $site_block_model->getById('flexdiscount.product.discounts');
    if (!$block_pd) {
        $file_pd = dirname(__FILE__) . '/../../config/data/flexdiscount.product.discounts.html';
        if (file_exists($file_pd)) {
            $block_content_pd = file_get_contents($file_pd);
            $site_block_model->add(array(
                "id" => "flexdiscount.product.discounts",
                "content" => $block_content_pd,
                "description" => "Действующие скидки для товара",
            ));
        }
    }
} catch (Exception $ex) {
    
}

// Обновляем блоки. Создаем копии старых
try {
    // Форма для ввода купона
    $block_form = $site_block_model->getById('flexdiscount.form');
    $block_form_old = $site_block_model->getById('flexdiscount.form_old');
    if ($block_form && !$block_form_old) {
        if ($site_block_model->updateById('flexdiscount.form', array('id' => 'flexdiscount.form_old'))) {
            $file_form = dirname(__FILE__) . '/../../config/data/flexdiscount.form.html';
            if (file_exists($file_form)) {
                $block_content_form = file_get_contents($file_form);
                $site_block_model->add(array(
                    "id" => "flexdiscount.form",
                    "content" => $block_content_form,
                    "description" => "Форма для ввода купона",
                ));
            }
        }
    }
} catch (Exception $ex) {
    
}

try {
    // Примененные скидки
    $block_discounts = $site_block_model->getById('flexdiscount.discounts');
    $block_discounts_old = $site_block_model->getById('flexdiscount.discounts_old');
    if ($block_discounts && !$block_discounts_old) {
        if ($site_block_model->updateById('flexdiscount.discounts', array('id' => 'flexdiscount.discounts_old'))) {
            $file_discounts = dirname(__FILE__) . '/../../config/data/flexdiscount.discounts.html';
            if (file_exists($file_discounts)) {
                $block_content_discounts = file_get_contents($file_discounts);
                $site_block_model->add(array(
                    "id" => "flexdiscount.discounts",
                    "content" => $block_content_discounts,
                    "description" => "Примененные скидки",
                ));
            }
        }
    }
} catch (Exception $ex) {
    
}

try {
    // Начисленные бонусы
    $block_affiliate = $site_block_model->getById('flexdiscount.affiliate');
    $block_affiliate_old = $site_block_model->getById('flexdiscount.affiliate_old');
    if ($block_affiliate && !$block_affiliate_old) {
        if ($site_block_model->updateById('flexdiscount.affiliate', array('id' => 'flexdiscount.affiliate_old'))) {
            $file_affiliate = dirname(__FILE__) . '/../../config/data/flexdiscount.affiliate.html';
            if (file_exists($file_affiliate)) {
                $block_content_affiliate = file_get_contents($file_affiliate);
                $site_block_model->add(array(
                    "id" => "flexdiscount.affiliate",
                    "content" => $block_content_affiliate,
                    "description" => "Начисленные бонусы",
                ));
            }
        }
    }
} catch (Exception $ex) {
    
}

try {
    // Старая версия блока доступных скидок
    $block_all = $site_block_model->getById('flexdiscount.all');
    if ($block_all) {
        $site_block_model->updateById('flexdiscount.all', array('id' => 'flexdiscount.all_old', "description" => "Блок не будет работать в новой версии плагина."));
    }
} catch (Exception $ex) {
    
}