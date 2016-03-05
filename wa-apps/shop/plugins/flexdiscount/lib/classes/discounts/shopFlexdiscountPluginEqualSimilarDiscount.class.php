<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке Х одинаковых товаров устанавливается скидка на эти товары
 * 
 * Маска: "=%num"
 */

class shopFlexdiscountPluginEqualSimilarDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = 0;
        $items = $order['items'];
        $mask_info = self::getMaskInfo();

        // Товары, которые прошли проверку и которым была назначена скидка
        $checked_items = array();

        preg_match("/\d+/", $params['value'], $matches);
        $user_count = reset($matches);

        $discounteachitem = !empty($mask_info['discountEachItem']) && $params['discounteachitem'];
        foreach ($items as $item) {
            // Проверяем принадлежность товара к указанным категории и типу
            if (!self::itemCheck($params, $item, $categories, $contact)) {
                continue;
            }

            $item['quantity'] = (int) $item['quantity'];
            if ($user_count == $item['quantity']) {
                $item_discount = 0;
                if ($params['discount_percentage']) {
                    $item_discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $item['price'] * $item['quantity'] / 100.0;
                }
                if ($params['discount'] && $params['discount'] > 0) {
                    $fix_discount = $params['discount'];
                    if ($discounteachitem) {
                        $fix_discount *= $item['quantity'];
                    }
                    $item_discount += $fix_discount;
                }

                // Если скидка для товара больше его цены, то устанавливаем скидку в размере цены товара
                if ($item['price'] * $item['quantity'] < $item_discount) {
                    $item_discount = $item['price'] * $item['quantity'];
                }
                $discount += $item_discount;

                $checked_items[$item['sku_id']] = array(
                    'quantity' => $item['quantity'],
                    'discount' => $item_discount / $item['quantity'],
                    'total_discount' => $item_discount,
                    'custom_params' => array()
                );
                if ($params['affiliate_percentage']) {
                    $affiliate += max(0.0, (float) $params['affiliate_percentage']) * $item['price'] * $item['quantity'] / 100.0;
                }
                if ($params['affiliate'] && $params['affiliate'] > 0) {
                    $affiliate += $params['affiliate'] * ($discounteachitem ? $item['quantity'] : 1);
                }
            }
        }

        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
