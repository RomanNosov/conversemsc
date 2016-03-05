<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке X любых товаров устанавливается скидка Y товаров самой низкой цены из этого списка
 * 
 * Маска: "numX#numY#"
 */

class shopFlexdiscountPluginDependNonSimilarSingleDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = $quantity = 0;
        $items = $order['items'];
        $product_price = array();

        preg_match_all("/\d+/", $params['value'], $matches);
        if (!empty($matches[0][0])) {
            $user_count1 = $matches[0][0];
            $user_count2 = $matches[0][1];

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

                $quantity += (int) $item['quantity'];
            }
            $mult = floor($quantity / $user_count1);
            // Если была хотя бы одна низкая цена
            if ($mult >= 1 && count($product_price) > 1) {
                ksort($product_price);
                $price = reset($product_price);
                $mult *= $user_count2;

                // Переданное количество товаров при расчете цены товара со скидкой
                $imagine_quantity = shopFlexdiscountHelper::getImagineQuantity();

                if ($mult > $price['quantity']) {
                    $mult = $price['quantity'];
                }
                if ($params['discount_percentage']) {
                    $discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $price['price'] * $mult / 100.0;
                }
                if ($params['discount'] && $params['discount'] > 0) {
                    $discount += $params['discount'] * $mult;
                }
                // Если скидка для товара больше его цены, то устанавливаем скидку в размере цены товара
                if ($price['price'] * $price['quantity'] < $discount) {
                    $discount = $price['price'] * $mult;
                }

                $checked_items[$price['sku_id']] = array(
                    'quantity' => $mult, 
                    'discount' => $discount / $mult,
                    'total_discount' => $discount,
                    'custom_params' => array()
                );
                // Если необходимо рассчитать цену товара со скидкой
                if ($imagine_quantity) {
                    // Получаем значения скидок для корзины
                    $cart_workflow = shopFlexdiscountWorkflow::getCartWorkflow(true);
                    if ($cart_workflow) {
                        $cart_discount = !empty($cart_workflow['discount_name'][$params['id']]['skus'][$price['sku_id']]) ? $cart_workflow['discount_name'][$params['id']]['skus'][$price['sku_id']]['discount'] : 0;
                        $checked_items[$price['sku_id']]['discount'] = ($discount - $cart_discount) / $imagine_quantity;
                    }
                }
                if ($params['affiliate_percentage']) {
                    $affiliate += max(0.0, (float) $params['affiliate_percentage']) * $price['price'] * $mult / 100.0;
                }
                if ($params['affiliate'] && $params['affiliate'] > 0) {
                    $affiliate += $params['affiliate'] * $mult;
                }
            }
        }

        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
