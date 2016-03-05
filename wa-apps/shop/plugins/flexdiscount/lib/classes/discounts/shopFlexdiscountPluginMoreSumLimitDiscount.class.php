<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке больше X любых единиц товара устанавливается скидка на все товары при условии, что сумма покупки не менее Z
 * 
 * Маска: ">numX%sumZ"
 */

class shopFlexdiscountPluginMoreSumLimitDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $quantity = $affiliate = $price = 0;
        $items = $order['items'];
        $mask_info = self::getMaskInfo();

        preg_match_all("/\d+/", $params['value'], $matches);

        if (!empty($matches[0][0])) {
            $num = $matches[0][0];
            $min_sum = $matches[0][1];

            // Товары, которые прошли проверку и которым была назначена скидка
            $checked_items = array();

            foreach ($items as $item) {
                // Проверяем принадлежность товара к указанным категории и типу
                if (!self::itemCheck($params, $item, $categories, $contact)) {
                    continue;
                }
                $quantity += (int) $item['quantity'];
                $price += $item['price'] * $item['quantity'];

                $checked_items[$item['sku_id']] = array(
                    'quantity' => $item['quantity'],
                    'discount' => $params['discount_percentage'] ? max(0.0, min(100.0, (float) $params['discount_percentage'])) * $item['price'] / 100.0 : 0,
                    'custom_params' => array(),
                );
                $checked_items[$item['sku_id']]['total_discount'] = $checked_items[$item['sku_id']]['discount'] * $item['quantity'];
            }
            if ($num < $quantity && $min_sum < $price) {
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
        }
        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
