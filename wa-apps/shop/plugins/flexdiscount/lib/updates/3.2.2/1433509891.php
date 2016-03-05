<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
try {
    $model->exec("SELECT domain_id FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD domain_id INT (11) NOT NULL DEFAULT '0'");
}

try {
    $index_exist = $model->query("SHOW INDEX FROM shop_flexdiscount  WHERE Key_name = 'm_v_c_t_c_s_c'")->fetch();
    if ($index_exist) {
        // Удаляем старый индекс
        $model->exec("ALTER TABLE shop_flexdiscount DROP INDEX m_v_c_t_c_s_c");
        // Добавляем новый 
        $model->exec("ALTER TABLE shop_flexdiscount ADD UNIQUE `m_v_c_t_c_s_c_d` (`mask`, `value`, `category_id`, `type_id`, `coupon_id`, `set_id`, `contact_category_id`, `domain_id`)");
    }
} catch (waDbException $e) {
    
}