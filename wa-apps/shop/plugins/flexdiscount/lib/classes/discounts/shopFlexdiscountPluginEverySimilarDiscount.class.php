<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * Скидка  на каждый i-й одинаковый товар
 * 
 * Маска: "%num"
 */

class shopFlexdiscountPluginEverySimilarDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = 0;
        $items = $order['items'];
        
        // Товары, которые прошли проверку и которым была назначена скидка
        $checked_items = array();

        preg_match("/\d+/", $params['value'], $matches);
        $user_count = reset($matches);

        // Переданное количество товаров при расчете цены товара со скидкой
        $imagine_quantity = shopFlexdiscountHelper::getImagineQuantity();

        foreach ($items as $item) {
            // Проверяем принадлежность товара к указанным категории и типу
            if (!self::itemCheck($params, $item, $categories, $contact)) {
                continue;
            }
            $item['quantity'] = (int) $item['quantity'];
            $mult = floor($item['quantity'] / $user_count);
            if ($mult >= 1) {
                $item_discount = 0;
                if ($params['discount_percentage']) {
                    $item_discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $item['price'] * $mult / 100.0;
                }
                if ($params['discount'] && $params['discount'] > 0) {
                    $item_discount += $params['discount'] * $mult;
                }
                // Если скидка для товара больше его цены, то устанавливаем скидку в размере цены товара
                if ($item['price'] * $mult < $item_discount) {
                    $item_discount = $item['price'] * $mult;
                }
                $discount += $item_discount;

                $checked_items[$item['sku_id']] = array(
                    'quantity' => $mult,
                    'discount' => $item_discount / $mult,
                    'total_discount' => $item_discount,
                    'custom_params' => array()
                );
                // Если необходимо рассчитать цену товара со скидкой
                if ($imagine_quantity) {
                    // Получаем значения скидок для корзины
                    $cart_workflow = shopFlexdiscountWorkflow::getCartWorkflow(true);
                    if ($cart_workflow) {
                        $cart_discount = !empty($cart_workflow['discount_name'][$params['id']]['skus'][$item['sku_id']]) ? $cart_workflow['discount_name'][$params['id']]['skus'][$item['sku_id']]['discount'] : 0;
                        $checked_items[$item['sku_id']]['discount'] = ($item_discount - $cart_discount) / $imagine_quantity;
                    }
                }
                if ($params['affiliate_percentage']) {
                    $affiliate += max(0.0, (float) $params['affiliate_percentage']) * $item['price'] * $mult / 100.0;
                }
                if ($params['affiliate'] && $params['affiliate'] > 0) {
                    $affiliate += $params['affiliate'] * $mult;
                }
            }
        }

        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
