<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке более(или равно) X наборов (по одной уникальной единице) товаров из указанной категории начисляется скидка на весь заказ
 * 
 * Маска: ">=set"
 */

class shopFlexdiscountPluginMoreEqualSetAllDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = $price = 0;
        $items = $order['items'];
        $user_products = array();
        preg_match("/\d+/", $params['value'], $matches);
        $user_count = reset($matches);
        $hash = '';

        // Товары, которые прошли проверку и которым была назначена скидка
        $checked_items = array();

        // Узнаем количество товаров в категории
        if ($params['category_id']) {
            $hash = 'category/' . $params['category_id'];
        }
        $pc = new shopProductsCollection($hash);
        if ($params['type_id']) {
            waRequest::setParam('type_id', array($params['type_id']));
        }
        $pr = $pc->getProducts("*", 0, 100000);

        if ($params['set_id']) {
            $sets = self::getSets();
            if (isset($sets[$params['set_id']])) {
                $pr = array_intersect(array_keys($pr), $sets[$params['set_id']]);
            }
        }

        $products_count = count($pr);

        foreach ($items as $item) {
            $price += (int) $item['price'] * $item['quantity'];
            // Проверяем принадлежность товара к указанным категории и типу
            if (!self::itemCheck($params, $item, $categories, $contact)) {
                continue;
            }
            if (!isset($user_products[$item['product_id']])) {
                $user_products[$item['product_id']] = $item['quantity'];
            } else {
                $user_products[$item['product_id']] += $item['quantity'];
            }

            $checked_items[$item['sku_id']] = array(
                'quantity' => $item['quantity'],
                'discount' => $params['discount_percentage'] ? max(0.0, min(100.0, (float) $params['discount_percentage'])) * $item['price'] / 100.0 : 0,
                'custom_params' => array(),
            );
            $checked_items[$item['sku_id']]['total_discount'] = $checked_items[$item['sku_id']]['discount'] * $item['quantity'];
        }
        if ($user_products) {
            $count_user_products = count($user_products);
            // Если количество купленных товаров совпадает с количеством товаров в категории, т.е был
            // куплен набор и количество наборов превышает(или равно) заданный, то продолжаем обработку
            if ($count_user_products == $products_count && min($user_products) >= $user_count) {

                if ($params['discount_percentage']) {
                    $discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $price / 100.0;
                }
                if ($params['discount'] && $params['discount'] > 0) {
                    $discount += $params['discount'];
                }
                if ($params['affiliate_percentage']) {
                    $affiliate += max(0.0, (float) $params['affiliate_percentage']) * $price / 100.0;
                }
                if ($params['affiliate'] && $params['affiliate'] > 0) {
                    $affiliate += $params['affiliate'];
                }
            }
        }
        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
