<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountMask
{

    // Выборка по динамичным товарам
    private static $dynamic_products = array();
    // Категории, типы, списки, попадающие под запрет скидок
    private static $deny_items = array();
    // Дерево категорий
    private static $category_tree = array();
    // Товары, которые не прошли проверку категорий или типов.
    // Необходимы для проверки принадлежности переданных сервисов к товарам
    private static $blocked_products = array();
    // Информация о действующем купоне
    private static $coupon_info = array();
    // Списки товаров
    private static $sets = array();
    // Информация о применяемой маске
    private static $mask_info = array();
    // Информация о всех мсках
    private static $config_mask = array();
    

    /**
     * Get mask discount
     * 
     * @param array $params 
     * @param array $order
     * @param array $categories
     * @param array $contact
     * @param bool $apply
     * @return float|int
     */
    public static function getDiscount($params, $order, $categories, $contact, $apply)
    {
        // Получаем маски
        // Маски, созданные пользователем
        $user_mask_path = dirname(__FILE__) . "/../config/custom_mask.php";
        $user_mask = file_exists($user_mask_path) ? include $user_mask_path : array();

        $config = include dirname(__FILE__) . "/../config/config.php";
        $config_mask = $config['mask'];
        self::$config_mask = array_merge($config_mask, $user_mask);

        // Если не существует скидки, то прерываем ее обработку
        if (!isset(self::$config_mask[$params['mask']])) {
            return 0;
        }
        
        // Проводим проверку купонов для всех скидок, кроме "отмены"
        if ($params['mask'] !== '-') {
            if (!self::$coupon_info) {
                // Получаем действующий купон
                $coupon_code = wa()->getStorage()->get("flexdiscount-coupon");
                self::$coupon_info = shopFlexdiscountHelper::couponCheck($coupon_code);
            }
            // Если скидка закреплена за другим купоном, то прерываем ее обработку
            if (self::$coupon_info && $params['coupon_id'] && self::$coupon_info['id'] !== $params['coupon_id']) {
                return 0;
            } elseif (!self::$coupon_info && $params['coupon_id']) {
                return 0;
            }
        }
        // Проверка времени действия скидки
        if (isset($params['expire_datetime']) && strtotime($params['expire_datetime']) < time()) {
            return 0;
        }

        // Проверяем принадлежность пользователя к категории контакта
        if ($params['contact_category_id'] && $contact) {
            $wcc = new waContactCategoriesModel();
            // Если пользователь не принадлежит категории, то прерываем обработку
            if (!$wcc->inCategory($contact->getId(), $params['contact_category_id'])) {
                return 0;
            }
        }
        // Если не существует дерева категорий, то создаем его
        self::getCategoryTree();

        // Если установлен запрет скидок
        if ($params['mask'] == '-') {
            return self::deny($params, $order, $categories, $contact, $apply);
        }

        // Подключаем класс скидок
        $class_name = sprintf("shopFlexdiscountPlugin%sDiscount", ucfirst(self::$config_mask[$params['mask']]['module']));
        $path = wa()->getAppPath('plugins/flexdiscount/lib/classes/discounts/' . $class_name . '.class.php');
        if (file_exists($path)) {
            require_once($path);
        } else {
            return 0;
        }

        if (class_exists($class_name)) {
            $mask_class = new $class_name();
            if (!($mask_class instanceof self)) {
                return 0;
            }

            self::$mask_info = self::$config_mask[$params['mask']];

            $discount = $mask_class->execute($params, $order, $categories, $contact, $apply);
            // Если была применена скидка, то запоминаем ее, чтобы вывести в шаблон
            if (is_array($discount)) {
                $return_discount = array(
                    "discount" => $discount['discount'],
                    "affiliate" => $discount['affiliate'],
                    "name" => $params['name'],
                    "skus" => $discount['skus'],
                    "coupon" => ($apply && $params['coupon_id']) ? $params['coupon_id'] : 0
                );
                if (isset($discount['items'])) {
                    $return_discount['items'] = $discount['items'];
                }
                return $return_discount;
            }
        }

        return 0;
    }

    /**
     * Check if item belongs to deny category/type/set
     * 
     * @param array[mixed] $item - cart item
     * @param array[mixed] $categories - all categories
     * @param string $set_id
     * @param int $cid - contact id
     * @return bool
     */
    protected static function denyCheck($item, $categories, $set_id, $cid)
    {
        if (self::$deny_items) {
            // Если обрабатывается Услуга , то отменяем начисление скидки на Услугу
            if ($item['type'] == 'service') {
                return false;
            }
            // Обрабатываем только Товары
            else if ($item['type'] !== 'service') {
                foreach (self::$deny_items as $deny_category => $deny_types) {
                    // Если категория динамическая
                    if (self::$category_tree[$deny_category]['type']) {
                        // Если выборки по данной категории не было, то делаем запрос
                        if (!isset(self::$dynamic_products[$deny_category])) {
                            // Получаем все товары динамической категории
                            $pc = new shopProductsCollection('category/' . $deny_category);
                            self::$dynamic_products[$deny_category] = $pc->getProducts("*", 0, 10000);
                        }
                        // Если среди товаров нету искомого, то прерываем процесс
                        if (isset(self::$dynamic_products[$deny_category][$item['product']['id']])) {
                            // Если товар был уже проверен и добавлен в запрет, то прерываем обработку
                            if (isset(self::$blocked_products[$item['product']['id']])) {
                                return false;
                            }
                            if (isset(self::$deny_items[0][$item['product']['type_id']][$set_id][$cid]) || isset(self::$deny_items[0][0][$set_id][$cid]) || isset(self::$deny_items[0][$item['product']['type_id']][0][$cid]) || isset(self::$deny_items[0][0][0][$cid]) || ((isset(self::$deny_items[$deny_category][$item['product']['type_id']][$set_id][$cid]) || isset(self::$deny_items[$deny_category][0][$set_id][$cid]) || isset(self::$deny_items[$deny_category][0][0][$cid]) || isset(self::$deny_items[$deny_category][$item['product']['type_id']][0][$cid]))
                                    )
                            ) {
                                return false;
                            }
                        }
                    }
                    // Если категория обычная
                    else {
                        if ((isset($categories[$deny_category]) && in_array($item['product']['id'], $categories[$deny_category])) || isset(self::$deny_items[0])) {
                            // Если товар был уже проверен и добавлен в запрет, то прерываем обработку
                            if (isset(self::$blocked_products[$item['product']['id']])) {
                                return false;
                            }
                            if (isset(self::$deny_items[0][$item['product']['type_id']][$set_id][$cid]) || isset(self::$deny_items[0][0][$set_id][$cid]) || isset(self::$deny_items[0][$item['product']['type_id']][0][$cid]) || isset(self::$deny_items[0][0][0][$cid]) || ((isset(self::$deny_items[$deny_category][$item['product']['type_id']][$set_id][$cid]) || isset(self::$deny_items[$deny_category][0][$set_id][$cid]) || isset(self::$deny_items[$deny_category][0][0][$cid]) || isset(self::$deny_items[$deny_category][$item['product']['type_id']][0][$cid])) && (isset($categories[$deny_category]) && in_array($item['product']['id'], $categories[$deny_category]))
                                    )
                            ) {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check, if item is ready to get discount 
     * Method checks categories, types and sets of the item
     * 
     * @param array $params - discount info
     * @param array $item
     * @param array $categories
     * @param object $contact
     * @param bool $deny_check 
     * @return boolean
     */
    protected static function itemCheck($params, $item, $categories, $contact = 0, $deny_check = true)
    {
        $category_id = $params['category_id'];
        $type_id = $params['type_id'];
        $set_id = $params['set_id'];
        // Если обрабатывается Услуга, то отменяем начисление скидки на Услугу
        if ($item['type'] == 'service') {
            return false;
        }
        // Обрабатываем только Товары
        else {
            // Проверка запретных категорий
            if ($deny_check) {
                 if ($contact) {
                    $contact_id = $contact->getId() ? $contact->getId() : 0;
                } else {
                    $contact_id = 0;
                }
                if (!self::denyCheck($item, $categories, $set_id, $contact_id)) {
                    return false;
                }
            }
            // Если имеется фильтр по категории
            if ($category_id) {
                // Если категория не динамическая и товар не принадлежит категории, то прерываем процесс
                // Если имеются подкатегории, то добавляем их под запрет
                if (isset(self::$category_tree[$category_id])) {
                    $children = self::$category_tree[$category_id]['children'];
                    if (!self::$category_tree[$category_id]['type']) {
                        $continue = false;
                        foreach ($children as $id => $child) {
                            if (isset($categories[$child]) && in_array($item['product']['id'], $categories[$child])) {
                                $continue = true;
                            }
                        }
                        if (!$continue) {
                            return false;
                        }
                    } else {
                        // Если категория динамическая
                        // Если выборки по данной категории не было, то делаем запрос
                        if (!isset(self::$dynamic_products[$category_id])) {
                            // Получаем все товары динамической категории
                            $pc = new shopProductsCollection('category/' . $category_id);
                            self::$dynamic_products[$category_id] = $pc->getProducts("*", 0, 10000);
                        }
                        // Если среди товаров нету искомого, то прерываем процесс
                        $continue = false;
                        foreach ($children as $id => $child) {
                            if (isset(self::$dynamic_products[$child][$item['product']['id']])) {
                                $continue = true;
                            }
                        }
                        if (!$continue) {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            }

            // Если имеется фильтр по типу товара
            if ($type_id) {
                // Если типы не совпадают, то удаляем товар
                if ($item['product']['type_id'] !== $type_id) {
                    return false;
                }
            }

            // Если имеется фильтр по спискам товаров
            if ($set_id) {
                $sets = self::getSets();
                if (isset(self::$sets[$set_id]) && !in_array($item['product']['id'], self::$sets[$set_id])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Build category tree
     * 
     * @return array
     */
    protected static function getCategoryTree()
    {
        if (self::$category_tree) {
            return self::$category_tree;
        }
        $category_model = new shopCategoryModel();
        $categories = $category_model->getTree(null);
        // Указатели на узлы дерева
        $tree = array();
        $tree[0]['path'] = array();
        $finish = false;
        // Не заканчиваем, пока не закончатся категории, или пока ни одну из оставшихся некуда будет деть
        while (!empty($categories) && !$finish) {
            $flag = false;
            foreach ($categories as $k => $category) {
                if (isset($tree[$category['parent_id']])) {
                    $tree[$category['id']] = $category;
                    $tree[$category['id']]['path'] = array_merge((array) $tree[$category['parent_id']]['path'], array($tree[$category['id']]));
                    unset($categories[$k]);
                    $flag = true;
                }
            }
            if (!$flag) {
                $finish = true;
            }
        }
        $ids = array_reverse(array_keys($tree));
        foreach ($ids as $id) {
            if ($id > 0) {
                $tree[$id]['children'][] = $id;
                if (isset($tree[$tree[$id]['parent_id']]['children'])) {
                    $tree[$tree[$id]['parent_id']]['children'] = array_merge($tree[$id]['children'], $tree[$tree[$id]['parent_id']]['children']);
                } else {
                    $tree[$tree[$id]['parent_id']]['children'] = $tree[$id]['children'];
                }
            }
        }
        $tree[0]['children'] = array(0);
        $tree[0]['type'] = 0;

        self::$category_tree = $tree;
    }
    
    /**
     * Get sets and their product IDs
     * 
     * @return array
     */
    protected static function getSets()
    {
        
        if (!self::$sets) {
            $ssp = new shopSetProductsModel();
            $sm = new shopSetModel();
            $sql = "SELECT ssp.product_id, s.id FROM {$sm->getTableName()} s LEFT JOIN {$ssp->getTableName()} ssp ON ssp.set_id = s.id";
            foreach($sm->query($sql) as $r) {
                self::$sets[$r['id']][] = $r['product_id'];
            }
        }
        return self::$sets;
    }

    /**
     * Get coupon information
     * 
     * @return array
     */
    protected static function getCouponInfo()
    {
        return self::$coupon_info;
    }

    /**
     * Get mask information
     * 
     * @return array
     */
    protected static function getMaskInfo()
    {
        return self::$mask_info;
    }
    
    /**
     * Get information about all masks 
     * 
     * @return array
     */
    protected static function getConfigMask()
    {
        return self::$config_mask;
    }
    
    /**
     * Deny rule for discounts
     * 
     * @param array $params
     * @param array $order
     * @param array $categories
     * @param array $contact
     * @param bool $apply
     * @return array
     */
    private static function deny($params, $order, $categories, $contact, $apply)
    {
        // Если имеются подкатегории, то добавляем их под запрет
        if (isset(self::$category_tree[$params['category_id']])) {
            $children = self::$category_tree[$params['category_id']]['children'];
            if ($contact) {
                $contact_id = $contact->getId() ? $contact->getId() : 0;
            } else {
                $contact_id = 0;
            }
            foreach ($children as $child) {
                self::$deny_items[$child][$params['type_id']][$params['set_id']][$contact_id] = $params['type_id'];
            }
        }
        // Если необходимо запретить список
        if ($params['set_id']) {
            $sets = self::getSets();
            if (isset($sets[$params['set_id']])) {
                foreach ($sets[$params['set_id']] as $sp) {
                    self::$blocked_products[$sp] = $sp;
                }
            }
        }
        return array("deny" => 1);
    }

}
