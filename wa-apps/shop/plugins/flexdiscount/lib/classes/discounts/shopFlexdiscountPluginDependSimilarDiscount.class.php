<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 * 
 * При покупке X одинаковых товаров устанавливается скидка на Y товаров из этого списка
 * 
 * Маска: "numX%numY"
 */

class shopFlexdiscountPluginDependSimilarDiscount extends shopFlexdiscountMask
{

    public function execute($params, $order, $categories, $contact, $apply)
    {
        $discount = $affiliate = 0;
        $items = $order['items'];

        preg_match_all("/\d+/", $params['value'], $matches);

        if (!empty($matches[0][0])) {
            $user_count1 = $matches[0][0];
            $user_count2 = $matches[0][1];
            if ($user_count1 >= $user_count2) {
                $mask_info = self::getMaskInfo();

                // Товары, которые прошли проверку и которым была назначена скидка
                $checked_items = array();

                // Переданное количество товаров при расчете цены товара со скидкой
                $imagine_quantity = shopFlexdiscountHelper::getImagineQuantity();

                $discounteachitem = !empty($mask_info['discountEachItem']) && $params['discounteachitem'];
                foreach ($items as $item) {
                    // Проверяем принадлежность товара к указанным категории и типу
                    if (!self::itemCheck($params, $item, $categories, $contact)) {
                        continue;
                    }
                    $item['quantity'] = (int) $item['quantity'];
                    $mult = floor($item['quantity'] / $user_count1);
                    if ($mult >= 1) {
                        $item_discount = 0;
                        if ($params['discount_percentage']) {
                            $item_discount += max(0.0, min(100.0, (float) $params['discount_percentage'])) * $item['price'] * $user_count2 * $mult / 100.0;
                        }
                        if ($params['discount'] && $params['discount'] > 0) {
                            $fix_discount = $params['discount'] * $mult;
                            if ($discounteachitem) {
                                $fix_discount *= $user_count2;
                            }
                            $item_discount += $fix_discount;
                        }
                        // Если скидка для товара больше его цены, то устанавливаем скидку в размере цены товара
                        if ($item['price'] * $user_count2 * $mult < $item_discount) {
                            $item_discount = $item['price'] * $user_count2 * $mult;
                        }
                        $discount += $item_discount;

                        $checked_items[$item['sku_id']] = array(
                            'quantity' => $mult * $user_count2,
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
                            $affiliate += max(0.0, (float) $params['affiliate_percentage']) * $item['price'] * $user_count2 * $mult / 100.0;
                        }
                        if ($params['affiliate'] && $params['affiliate'] > 0) {
                            $affiliate += $params['affiliate'] * $user_count2 * $mult;
                        }
                    }
                }
            }
        }

        return ($discount || $affiliate) ? array("discount" => $discount, "affiliate" => $affiliate, "skus" => $checked_items) : 0;
    }

}
