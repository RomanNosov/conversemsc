<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке товаров на сумму, большую чем X, устанавливается скидка на определенную категорию, определенный тип товаров
 * 
 * Маска: ">sum"
 */

class shopFlexdiscountPluginSumDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = $price = $total_price = $quantity = 0;
        $items = $order['items'];
        $mask_info = self::getMaskInfo();

        if ($contact) {
            $contact_id = $contact->getId() ? $contact->getId() : 0;
        } else {
            $contact_id = 0;
        }

        // Товары, которые прошли проверку и которым была назначена скидка
        $checked_items = array();

        foreach ($items as $item) {
            if (!self::denyCheck($item, $categories, $params['set_id'], $contact_id)) {
                continue;
            }
            $total_price += $item['price'] * $item['quantity'];
            // Проверяем принадлежность товара к указанным категории и типу
            if (!self::itemCheck($params, $item, $categories, $contact, false)) {
                continue;
            }

            $price += $item['price'] * $item['quantity'];
            $quantity += (int) $item['quantity'];

            $checked_items[$item['sku_id']] = array(
                'quantity' => $item['quantity'],
                'discount' => $params['discount_percentage'] ? max(0.0, min(100.0, (float) $params['discount_percentage'])) * $item['price'] / 100.0 : 0,
                'custom_params' => array(),
            );
            $checked_items[$item['sku_id']]['total_discount'] = $checked_items[$item['sku_id']]['discount'] * $item['quantity'];
        }

        $param_v = shop_currency(substr($params['value'], 1), null, null, false);
        if ($total_price > $param_v) {
            $discounteachitem = !empty($mask_info['discountEachItem']) && $params['discounteachitem'];
            if ($params['discount_percentage']) {
                $discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $price / 100.0;
            }
            if ($params['discount'] && $params['discount'] > 0) {
                $fix_discount = $params['discount'];
                if ($discounteachitem) {
                    $fix_discount *= $quantity;
                    foreach ($checked_items as &$i) {
                        $i['discount'] += $params['discount'];
                        $i['total_discount'] += $params['discount'] * $i['quantity'];
                    }
                }
                $discount += $fix_discount;
            }
            if ($params['affiliate_percentage']) {
                $affiliate += max(0.0, (float) $params['affiliate_percentage']) * $price / 100.0;
            }
            if ($params['affiliate'] && $params['affiliate'] > 0) {
                $affiliate += $params['affiliate'] * ($discounteachitem ? $quantity : 1);
            }
        }

        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
