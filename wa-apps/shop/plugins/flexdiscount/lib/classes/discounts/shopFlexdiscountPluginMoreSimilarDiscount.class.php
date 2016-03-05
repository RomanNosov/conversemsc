<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке больше X одинаковых товаров устанавливается скидка на все товары из этого списка
 * 
 * Маска: ">%num"
 */

class shopFlexdiscountPluginMoreSimilarDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = 0;
        $result = $products = array();
        $items = $order['items'];

        preg_match("/\d+/", $params['value'], $matches);
        $user_count = reset($matches);

        // Товары, которые прошли проверку и которым была назначена скидка
        $checked_items = array();

        // Настройки
        $settings = shopFlexdiscountHelper::getSettings();
        $count_method = $settings['count_method'];
        $count_items = count($items);

        foreach ($items as $item_id => $item) {
            // Проверяем принадлежность товара к указанным категории и типу
            if (!self::itemCheck($params, $item, $categories, $contact)) {
                continue;
            }
            $products[$item['product_id']]['quantity'][$item['sku_id']] = $item['quantity'];
            $products[$item['product_id']]['price'][$item['sku_id']] = $item['price'] * $item['quantity'];
            $item['quantity'] = (int) $item['quantity'];

            // Если было выбрано значение настроек "подсчет количества товаров по артикулу" 
            if ($count_method !== 'product') {
                if ($user_count < $item['quantity']) {
                    $item_discount = $affiliate_value = 0;

                    if ($params['discount_percentage']) {
                        $item_discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $item['price'] * $item['quantity'] / 100.0;
                    }
                    if ($params['discount'] && $params['discount'] > 0) {
                        $item_discount += $params['discount'] * $item['quantity'];
                    }

                    // Если скидка для товара больше его цены, то устанавливаем скидку в размере цены товара
                    if ($item['price'] * $item['quantity'] < $item_discount) {
                        $item_discount = $item['price'] * $item['quantity'];
                    }

                    $discount += $item_discount;

                    $checked_items[$item['sku_id']] = array(
                        'quantity' => $item['quantity'],
                        'discount' => $item_discount / $item['quantity'],
                        'custom_params' => array(),
                        'total_discount' => $item_discount
                    );

                    if ($params['affiliate_percentage']) {
                        $affiliate_value += max(0.0, (float) $params['affiliate_percentage']) * $item['price'] * $item['quantity'] / 100.0;
                    }
                    if ($params['affiliate'] && $params['affiliate'] > 0) {
                        $affiliate_value += $params['affiliate'] * $item['quantity'];
                    }
                    $affiliate += $affiliate_value;
                    if ($count_items > 1) {
                        $result[$item_id] = array('discount' => 0, 'affiliate' => 0);
                        $result[$item_id]['affiliate'] += $affiliate_value;
                        $result[$item_id]['discount'] += $item_discount;
                        $result[$item_id]['skus'] = $checked_items;
                    }
                }
            }
        }
        // Суммируем артикулы у товаров
        if ($count_method == 'product') {
            foreach ($products as $id => $value) {
                $item_discount = $affiliate_value = 0;
                $quantity = array_sum($value['quantity']);
                if ($user_count < $quantity) {
                    $price = array_sum($value['price']);
                    if ($params['discount_percentage']) {
                        $item_discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $price / 100.0;
                    }
                    if ($params['discount'] && $params['discount'] > 0) {
                        $item_discount += $params['discount'] * $quantity;
                    }

                    // Если скидка для товара больше его цены, то устанавливаем скидку в размере цены товара
                    if ($price * $quantity < $item_discount) {
                        $item_discount = $price * $quantity;
                    }

                    $discount += $item_discount;

                    foreach ($value['price'] as $sku_id => $v) {
                        $checked_items[$sku_id] = array(
                            'quantity' => $quantity,
                            'discount' => $item_discount / $quantity,
                            'custom_params' => array(),
                            'total_discount' => $item_discount
                        );
                    }

                    if ($params['affiliate_percentage']) {
                        $affiliate_value += max(0.0, (float) $params['affiliate_percentage']) * $price / 100.0;
                    }
                    if ($params['affiliate'] && $params['affiliate'] > 0) {
                        $affiliate_value += $params['affiliate'] * $quantity;
                    }
                    $affiliate += $affiliate_value;
                    if ($count_items > 1) {
                        $result[$id] = array('discount' => 0, 'affiliate' => 0);
                        $result[$id]['affiliate'] += $affiliate_value;
                        $result[$id]['discount'] += $item_discount;
                        $result[$id]['skus'] = $checked_items;
                    }
                }
            }
        }

        return ($discount || $affiliate) ? ($result ? array('items' => $result, 'discount' => $discount, 'affiliate' => $affiliate, 'skus' => $checked_items) : array("discount" => $discount, "affiliate" => $affiliate, 'skus' => $checked_items)) : 0;
    }

}
