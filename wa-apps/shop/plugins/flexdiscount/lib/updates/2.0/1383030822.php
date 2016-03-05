<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
// Проверяем существование поля id
try {
    $model->exec("SELECT id FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD id INT (11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
}
// Проверяем существование поля coupon_id
try {
    $model->exec("SELECT coupon_id FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD coupon_id INT (11) NOT NULL DEFAULT '0'");
}
// Проверяем существование поля name
try {
    $model->exec("SELECT name FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD name VARCHAR (100) NOT NULL DEFAULT ''");
}
// Проверяем существование поля expire_datetime
try {
    $model->exec("SELECT expire_datetime FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD expire_datetime DATETIME DEFAULT NULL");
}
try {
    // Проверяем, существует ли индекс mask_value_category_type или value_category_type   
    $index_exist = $model->query("SHOW INDEX FROM shop_flexdiscount  WHERE Key_name = 'mask_value_category_type'")->fetch();
    $index_exist2 = $model->query("SHOW INDEX FROM shop_flexdiscount  WHERE Key_name = 'value_category_type'")->fetch();
    if ($index_exist) {
        // Удаляем старый индекс
        $model->exec("ALTER TABLE shop_flexdiscount DROP INDEX mask_value_category_type");
        // Дабавляем новый mask_value_category_type_coupon
        $model->exec("ALTER TABLE shop_flexdiscount ADD UNIQUE `mask_value_category_type_coupon` (`mask`, `value`, `category_id`, `type_id`, `coupon_id`)");
    } elseif ($index_exist2) {
        // Удаляем старый индекс
        $model->exec("ALTER TABLE shop_flexdiscount DROP INDEX value_category_type");
        // Добавляем новый mask_value_category_type_coupon
        $model->exec("ALTER TABLE shop_flexdiscount ADD UNIQUE `mask_value_category_type_coupon` (`mask`, `value`, `category_id`, `type_id`, `coupon_id`)");
    }
} catch (waDbException $e) {
    
}
try {
    $sql = <<<SQL
  CREATE TABLE IF NOT EXISTS `shop_flexdiscount_coupon` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(16) NOT NULL,
    `limit` int(11) NOT NULL DEFAULT '-1',
    `used` int(11) NOT NULL DEFAULT '0',
    `create_datetime` datetime NOT NULL,
    `expire_datetime` datetime DEFAULT NULL,
    `comment` text NOT NULL,
    `color` varchar(6) NOT NULL DEFAULT '',
    `sort` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;
    $model->exec($sql);

    $sql2 = <<<SQL
    CREATE TABLE IF NOT EXISTS `shop_flexdiscount_coupon_order` (
        `coupon_id` int(11) NOT NULL,
        `order_id` int(11) NOT NULL,
        `discount` float(14,2) NOT NULL,
        `datetime` datetime NOT NULL,
        KEY `coupon` (`coupon_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8       
SQL;
    $model->exec($sql2);

    $sql3 = <<<SQL
    CREATE TABLE IF NOT EXISTS `shop_flexdiscount_settings` (
        `field` varchar(30) NOT NULL,
        `ext` varchar(30) NOT NULL DEFAULT '',
        `value` varchar(255) NOT NULL,
        UNIQUE KEY `field_ext` (`field`,`ext`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8     
SQL;
    $model->exec($sql3);

    // Все категории с отрицательным знаком переводим в положительный
    $model->exec("UPDATE `shop_flexdiscount` SET category_id = ABS(category_id) WHERE category_id < 0");
} catch (waDbException $e) {
    
}
// Перенос старых настроек
$count_method = wa()->getSetting('count_method', '', 'shop.flexdiscount');
if ($count_method !== 'product') {
    $count_method == 'sku';
}
$combine = wa()->getSetting('flexdiscount_combine', '', 'shop');
if ($combine !== 'sum') {
    $combine == 'max';
}
try {
    // Чистим таблицу настроек
    $asm = new waAppSettingsModel();
    $asm->del('shop.flexdiscount', 'count_method');
    $asm->del('shop', 'flexdiscount_combine');
    // Записываем новые настройки
    $sm = new shopFlexdiscountSettingsPluginModel();
    $sm->save(array(
        "combine" => $combine,
        "count_method" => $count_method,
    ));
} catch (Exception $e) {
    
}
// Добавление блоков
try {
    wa('site');
    $site_block_model = new siteBlockModel();
    // Форма для ввода купона
    $block_form = $site_block_model->getById('flexdiscount.form');
    if (!$block_form) {
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
    // Доступные скидки
    $block_availible = $site_block_model->getById('flexdiscount.all');
    if (!$block_availible) {
        $file_all = dirname(__FILE__) . '/../../config/data/flexdiscount.all.html';
        if (file_exists($file_all)) {
            $block_content_all = file_get_contents($file_all);
            $site_block_model->add(array(
                "id" => "flexdiscount.all",
                "content" => $block_content_all,
                "description" => "Все доступные скидки",
            ));
        }
    }
    // Активные скидки
    $block_discounts = $site_block_model->getById('flexdiscount.discounts');
    if (!$block_discounts) {
        $file_discounts = dirname(__FILE__) . '/../../config/data/flexdiscount.discounts.html';
        if (file_exists($file_discounts)) {
            $block_content_discounts = file_get_contents($file_discounts);
            $site_block_model->add(array(
                "id" => "flexdiscount.discounts",
                "content" => $block_content_discounts,
                "description" => "Действующие скидки",
            ));
        }
    }
} catch (Exception $e) {
    
}