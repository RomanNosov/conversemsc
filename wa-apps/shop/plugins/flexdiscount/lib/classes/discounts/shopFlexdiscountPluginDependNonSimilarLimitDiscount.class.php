<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке X любых товаров устанавливается скидка на 1шт Y единиц товаров самой низкой цены из этого списка. 
 * Скидка начинает работать при цене одного из товаров не ниже Z
 * 
 * Маска: "numX#numY#sumZ"
 */

class shopFlexdiscountPluginDependNonSimilarLimitDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = $quantity = 0;
        $items = $order['items'];
        $product_price = array();
        $break = true;

        preg_match_all("/\d+/", $params['value'], $matches);

        if (!empty($matches[0][0])) {
            $user_count1 = $matches[0][0];
            $user_count2 = $matches[0][1];
            $min_price = shop_currency($matches[0][2], null, null, false);

            // Товары, которые прошли проверку и которым была назначена скидка
            $checked_items = array();

            foreach ($items as $item) {
                // Проверяем принадлежность товара к указанным категории и типу
                if (!self::itemCheck($params, $item, $categories, $contact)) {
                    continue;
                }
                $product_price[(string) $item['price']] = array(
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sku_id' => $item['sku_id']
                );
                if ($item['price'] > $min_price) {
                    $break = false;
                }
                $quantity += (int) $item['quantity'];
            }
            $mult = floor($quantity / $user_count1);
            // Если была хотя бы одна низкая цена
            if ($mult >= 1 && count($product_price) > 1 && !$break) {
                ksort($product_price);
                $count = 1;

                // Переданное количество товаров при расчете цены товара со скидкой
                $imagine_quantity = shopFlexdiscountHelper::getImagineQuantity();

                foreach ($product_price as $pp) {
                    if ($count > $user_count2) {
                        break;
                    }
                    $item_discount = 0;
                    $item_mult = $mult;
                    if ($mult > $pp['quantity']) {
                        $item_mult = $pp['quantity'];
                    }
                    if ($params['discount_percentage']) {
                        $item_discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $pp['price'] * $item_mult / 100.0;
                    }
                    if ($params['discount'] && $params['discount'] > 0) {
                        $item_discount += $params['discount'] * $item_mult;
                    }
                    // Если скидка для товара больше его цены, то устанавливаем скидку в размере цены товара
                    if ($pp['price'] * $pp['quantity'] < $item_discount) {
                        $item_discount = $pp['price'] * $item_mult;
                    }
                    $discount += $item_discount;
                    $checked_items[$pp['sku_id']] = array(
                        'quantity' => $item_mult, 
                        'discount' => $item_discount / $item_mult,
                        'total_discount' => $item_discount,
                        'custom_params' => array()
                    );
                    // Если необходимо рассчитать цену товара со скидкой
                    if ($imagine_quantity) {
                        // Получаем значения скидок для корзины
                        $cart_workflow = shopFlexdiscountWorkflow::getCartWorkflow(true);
                        if ($cart_workflow) {
                            $cart_discount = !empty($cart_workflow['discount_name'][$params['id']]['skus'][$pp['sku_id']]) ? $cart_workflow['discount_name'][$params['id']]['skus'][$pp['sku_id']]['discount'] : 0;
                            $checked_items[$pp['sku_id']]['discount'] = ($item_discount - $cart_discount) / $imagine_quantity;
                        }
                    }
                    if ($params['affiliate_percentage']) {
                        $affiliate += max(0.0, (float) $params['affiliate_percentage']) * $pp['price'] * $item_mult / 100.0;
                    }
                    if ($params['affiliate'] && $params['affiliate'] > 0) {
                        $affiliate += $params['affiliate'] * $item_mult;
                    }
                    $count++;
                }
            }
        }

        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
