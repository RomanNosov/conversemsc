<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginSettingsAction extends waViewAction
{

    public function execute()
    {
        $fd = new shopFlexdiscountPluginModel();
        $cm = new shopFlexdiscountCouponPluginModel();
        $sm = new shopFlexdiscountSettingsPluginModel();
       
        // Получаем маски
        // Маски, созданные пользователем
        $user_mask_path = dirname(__FILE__) . "/../../config/custom_mask.php";
        $user_mask = file_exists($user_mask_path) ? include $user_mask_path : array();
        // Маски системные
        $config = include dirname(__FILE__) . "/../../config/config.php";
        $config_mask = $config['mask'];
        $config_mask = array_merge($config_mask, $user_mask);

        // Категории контакта
        $ccm = new waContactCategoryModel();
        $contact_categories = $ccm->getAll('id');

        // Категории товаров
        $scm = new shopCategoryModel();
        $categories = $scm->getTree(null);
        $categories = $this->getCategoriesTree($categories);

        // Списки товаров
        $ssm = new shopSetModel();
        $sets = $ssm->getByField('type', shopSetModel::TYPE_STATIC, 'id');

        // Типы товаров
        $stm = new shopTypeModel();
        $types = $stm->getTypes(true);

        // Текущая валюта
        $def_cur = waCurrency::getInfo(wa()->getConfig()->getCurrency());

        // Обрабатываем переданные скидки
        if (waRequest::post()) {
            // Записываем новые настройки
            $settings = waRequest::post('settings', '');
            $sm->save($settings);

            // Очищаем базу
            $fd->clear();
            $mask = waRequest::post('mask');
            $contact_mask = waRequest::post('contact_mask');
            $value = waRequest::post('value');
            $discount_percentage = waRequest::post('discount_percentage');
            $discount = waRequest::post('discount');
            $affiliate = waRequest::post('affiliate');
            $affiliate_percentage = waRequest::post('affiliate_percentage');
            $set = waRequest::post('set');
            $category = waRequest::post('category');
            $coupon_id = waRequest::post('coupon_id');
            $discounteachitem = waRequest::post('discounteachitem');
            $type = waRequest::post('type');
            $name = waRequest::post('name');
            $domain_id = waRequest::post('domain_id');
            $expire_datetime = waRequest::post('expire_datetime');
            $code = waRequest::post('code');
            $data = array();
            $all_mask = $config_mask + $contact_categories;
            if (is_array($mask) && is_array($value)) {
                foreach ($mask as $k => $m) {
                    $sort = $contact_category_id =0;
                    // Если переданной маски не существует, то пропускаем поля
                    if (!isset($all_mask[$m])) {
                        continue;
                    }

                    // Если выбрана категория контакта
                    if ((int) $m > 0) {
                        $contact_category_id = (int) $m;
                        $m = $contact_mask[$k];
                    }
                    // Если количество товаров удовлетворяет маске
                    if ($value[$k] = shopFlexdiscountHelper::isValidMask($m, $value[$k])) {

                        // Если выбрана отмена действия скидок или категория контакта, то обнуляем значения скидок
                        if ($m == '-') {
                            $discount_percentage[$k] = 0;
                            $discount[$k] = 0;
                            $affiliate[$k] = 0;
                            $affiliate_percentage[$k] = 0;
                            $coupon_id[$k] = 0;
                        }
                        // Если скидки не указаны, то пропускаем поля
                        elseif (!$discount_percentage[$k] && !$discount[$k] && !$affiliate[$k] && !$affiliate_percentage[$k]) {
                            continue;
                        } else {
                            $sort = $k + 1;
                        }

                        $data[] = array(
                            "mask" => $m,
                            "value" => $value[$k],
                            "discount_percentage" => (float) $discount_percentage[$k],
                            "discount" => $discount[$k],
                            "affiliate" => $affiliate[$k],
                            "affiliate_percentage" => (float) $affiliate_percentage[$k],
                            "set_id" => $set[$k],
                            "category_id" => $category[$k],
                            "contact_category_id" => $contact_category_id,
                            "discounteachitem" => !empty($discounteachitem[$k]) && !empty($all_mask[$m]['discountEachItem']) ? $discounteachitem[$k] : 0,
                            "coupon_id" => (int) $coupon_id[$k],
                            "type_id" => $type[$k],
                            "domain_id" => $domain_id[$k],
                            "name" => $name[$k],
                            "expire_datetime" => $expire_datetime[$k],
                            "sort" => $sort,
                            "code" => !empty($code[$k]) ? preg_replace("/[^a-zA-Z0-9\-_]/", "", $code[$k]) : ''
                        );
                    }
                }
                if ($data) {
                    $fd->multipleIgnoreInsert($data);
                }
            }

            // Обрабатываем купоны
            $post_coupons = waRequest::post('coupon');
            if ($post_coupons) {
                $all_coupons = $cm->getAll('id');
                $coupon_codes = $cm->select("code")->fetchAll(null, 1);
                $insert = array();
                foreach ($post_coupons['id'] as $k => $v) {
                    $coupon = array(
                        'id' => $v,
                        'color' => $post_coupons['color'][$k],
                        'code' => $post_coupons['code'][$k],
                        'limit' => $post_coupons['limit'][$k],
                        'expire_datetime' => $post_coupons['expire_datetime'][$k],
                        'comment' => $post_coupons['comment'][$k],
                    );
                    // Если не был введен код купона, то прерываем обработку
                    if (empty($coupon['code'])) {
                        continue;
                    }
                    // Проверяем код купона на совпадения.
                    // Если код совпадает с существующим, то генерируем новый не найдем уникальный код
                    $coupon['code'] = trim($coupon['code']);
                    if (in_array($coupon['code'], $coupon_codes) &&
                            (empty($coupon['id']) || (!empty($coupon['id']) && isset($all_coupons[$coupon['id']]) && $all_coupons[$coupon['id']]['code'] !== $coupon['code']))) {
                        do {
                            $coupon['code'] = shopFlexdiscountCouponPluginModel::generateCode();
                        } while (in_array($coupon['code'], $coupon_codes));
                    }
                    $coupon['code'] = strip_tags($coupon['code']);
                    if (strlen($coupon['code']) > 16) {
                        $coupon['code'] = mb_substr($coupon['code'], 0, 16, "UTF-8");
                    }

                    // Максимальное количество использований
                    $coupon['limit'] = (int) ifempty($coupon['limit'], -1);

                    // Дата истечения
                    $validator = new waRegexValidator(array(
                        'required' => false,
                        "pattern" => "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/"
                    ));
                    if (!empty($coupon['expire_datetime'])) {
                        $coupon['expire_datetime'] = trim($coupon['expire_datetime']);
                        if ($validator->isValid($coupon['expire_datetime'])) {
                            $coupon['expire_datetime'] .= ' 23:59:59';
                        } else {
                            $coupon['expire_datetime'] = null;
                        }
                    } else {
                        $coupon['expire_datetime'] = null;
                    }
                    // Цвет
                    if ($coupon['color']) {
                        $coupon['color'] = substr($coupon['color'], 0, 6);
                    }

                    if (!empty($coupon['id'])) {
                        // Обновляем купоны
                        $cm->updateById($coupon['id'], $coupon);
                    } else {
                        // Время создания купона
                        $coupon['create_datetime'] = date("Y-m-d H:i:s");
                        $insert[] = $coupon;
                    }
                }
                // Добавляем новые купоны
                if ($insert) {
                    $cm->multipleIgnoreInsert($insert);
                }
            }
        }

        // Получаем настройки
        $settings = $sm->getSettings();

        // Количество товаров на странице
//        $perpage = (!empty($settings['perpage']) ? $settings['perpage'] : 30); 
        // Действующие скидки
        $discounts = $fd->getDiscounts(true);

        // Общее количество скидок
//        $count = $fd->countAll();
//        
//        if (count($discounts) < $count) {
//            $this->view->assign('more_button', 1);
//        }
        // Купоны
        $coupons = $cm->getCoupons(true);

        $enabled = shopDiscounts::isEnabled('flexdiscount');

        // Витрины
        wa('site');
        $domain_model = new siteDomainModel();
        $domains = $domain_model->getAll('id');
        foreach($domains as &$dom) {
            $dom['name'] = $dom['title'] ? $dom['title'] : $dom['name'];
        }
        $this->view->assign('mask', $config_mask);
        $this->view->assign('discounts', $discounts);
        $this->view->assign('coupons', $coupons);
        $this->view->assign('enabled', $enabled);
        $this->view->assign('categories', $categories);
        $this->view->assign('sets', $sets);
        $this->view->assign('settings', $settings);
        $this->view->assign('contact_categories', $contact_categories);
        $this->view->assign('types', $types);
        $this->view->assign('domains', $domains);
        $this->view->assign('def_cur_sym', ifset($def_cur['sign'], wa()->getConfig()->getCurrency()));
        $this->view->assign('plugin_url', wa()->getPlugin('flexdiscount')->getPluginStaticUrl());
    }

    /**
     * Build categories tree
     * @param array $cats
     * @return array
     */
    private function getCategoriesTree($cats)
    {
        $stack = array();
        $result = array();
        foreach ($cats as $c) {
            $c['childs'] = array();
            // Number of stack items
            $l = count($stack);
            // Check if we're dealing with different levels
            while ($l > 0 && $stack[$l - 1]['depth'] >= $c['depth']) {
                array_pop($stack);
                $l--;
            }
            // Stack is empty (we are inspecting the root)
            if ($l == 0) {
                // Assigning the root node
                $i = count($result);
                $result[$i] = $c;
                $stack[] = & $result[$i];
            } else {
                // Add node to parent
                $i = count($stack[$l - 1]['childs']);
                $stack[$l - 1]['childs'][$i] = $c;
                $stack[] = & $stack[$l - 1]['childs'][$i];
            }
        }
        return $result;
    }

}
