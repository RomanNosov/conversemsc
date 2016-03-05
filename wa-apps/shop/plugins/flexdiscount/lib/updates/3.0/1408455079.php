<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();

try {
    $model->exec("SELECT set_id FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD set_id VARCHAR (255) NOT NULL DEFAULT ''");
}

try {
    $model->exec("SELECT contact_category_id FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD contact_category_id INT (11) NOT NULL DEFAULT '0'");
}

try {
    $model->exec("SELECT discounteachitem FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD discounteachitem INT (1) NOT NULL DEFAULT '0'");
}

try {
    // Обновляем маски
    $sfm = new shopFlexdiscountPluginModel();
    $sfm->updateByField("mask", "num%num", array("mask" => "numX%numY"));
    $sfm->updateByField("mask", "num#num", array("mask" => "numX#numY"));
    $sfm->updateByField("mask", "num#num#", array("mask" => "numX#numY#"));
    $sfm->updateByField("mask", "num#num#num", array("mask" => "numX#numY#sumZ"));
    $sfm->updateByField("mask", "num#num#num#", array("mask" => "numX#numY#sumZ#"));
} catch (waDbException $e) {
    
}

try {
    // Обновляем категории покупателей
    $categories = $sfm->select("id, mask")->where("value = 'contact'")->fetchAll('id');
    if ($categories) {
        foreach ($categories as $c) {
            $ccid = substr($c['mask'], 7);
            $sfm->updateById($c['id'], array(
                "mask" => "=",
                "value" => "=",
                "contact_category_id" => (int) $ccid
            ));
        }
    }
} catch (waDbException $e) {
    
}

try {
    $index_exist = $model->query("SHOW INDEX FROM shop_flexdiscount  WHERE Key_name = 'mask_value_category_type_coupon'")->fetch();
    if ($index_exist) {
        // Удаляем старый индекс
        $model->exec("ALTER TABLE shop_flexdiscount DROP INDEX mask_value_category_type_coupon");
        // Добавляем новый mask_value_category_type_coupon
        $model->exec("ALTER TABLE shop_flexdiscount ADD UNIQUE `m_v_c_t_c_s_c` (`mask`, `value`, `category_id`, `type_id`, `coupon_id`, `set_id`, `contact_category_id`)");
    }
} catch (waDbException $e) {
    
}