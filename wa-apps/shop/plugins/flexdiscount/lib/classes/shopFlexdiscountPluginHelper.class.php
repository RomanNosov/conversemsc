<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginHelper extends shopFlexdiscountMask
{

    // Обработанные скидки для товара
    private static $product_workflow = array();

    /**
     * Get active discounts
     * 
     * @return array[name, discount]|array
     */
    public static function getUserDiscounts()
    {
        $user_discounts = array();
        if (shopDiscounts::isEnabled('flexdiscount')) {
            // Получаем значения скидок для корзины
            $cart_workflow = shopFlexdiscountWorkflow::getCartWorkflow();
            if (!empty($cart_workflow['discount_name'])) {
                $settings = shopFlexdiscountHelper::getSettings();
                $round = isset($settings['round']) ? $settings['round'] : 'not';
                foreach ($cart_workflow['discount_name'] as $k => $v) {
                    $v['discount'] = ($round == 'ceil' ? ceil($v['discount']) : ($round == 'floor' ? floor($v['discount']) : ($round == 'tens' ? round($v['discount'], -1) : $v['discount'])));
                    $user_discounts[$k]['name'] = shopFlexdiscountHelper::secureString($v['name']);
                    $user_discounts[$k]['discount'] = shop_currency($v['discount'], true);
                    $user_discounts[$k]['discount_html'] = shop_currency_html($v['discount'], true);
                    $user_discounts[$k]['affiliate'] = (int) $v['affiliate'];
                }
            }
            // Передаем данные в блок
            return "<span class='flexdiscount-user-discounts fl-discounts'>" . shopFlexdiscountHelper::getBlock('flexdiscount.discounts', array(
                        'discounts' => $user_discounts
                    )) . "</span>";
        }
    }

    /**
     * Get product possible discount
     * 
     * @param array $product
     * @param string $view_type - type of display
     * @param int $sku_id - product sku ID
     * @return string - HTML
     */
    public static function getProductDiscounts($product, $view_type = null, $sku_id = '')
    {
        if (shopDiscounts::isEnabled('flexdiscount') && !empty($product)) {
            $product = ($product instanceof shopProduct) ? $product->getData() : $product;
            $return = "<span class='flexdiscount-product-discount product-id-" . $product['id'] . "' data-view-type='" . ($view_type ? shopFlexdiscountHelper::secureString($view_type) : '1') . "'";
            if (!$sku_id) {
                $sku_id = $product['sku_id'];
                $return .= " data-sku-id='0'";
            } else {
                $return .= " data-sku-id='" . $sku_id . "'";
            }
            $return .= ">";
            
            // Если товар уже обрабатывался, то возвращаем его данные
            if (!empty(self::$product_workflow[$product['id']][$sku_id])) {
                $workflow = self::$product_workflow[$product['id']][$sku_id];
            } else {
                $workflow = self::getCurrentDiscount($product, $sku_id, array(), 1, false);
            }
            // Передаем данные в блок
            return $return . shopFlexdiscountHelper::getBlock('flexdiscount.product.discounts', array(
                        'workflow' => $workflow,
                        'discounts' => $workflow['items'],
                        'view_type' => $view_type
                    )) . "</span>";
        }
    }

    /**
     * Get user affiliate
     * 
     * @return string - HTML
     */
    public static function getUserAffiliate()
    {
        if (shopDiscounts::isEnabled('flexdiscount') && shopAffiliate::isEnabled()) {
            $user_affiliate = 0;
            // Получаем значения скидок для корзины
            $cart_workflow = shopFlexdiscountWorkflow::getCartWorkflow();
            if (!empty($cart_workflow['discount_name'])) {
                foreach ($cart_workflow['discount_name'] as $v) {
                    $user_affiliate += (int) $v['affiliate'];
                }
            }
            return "<span class='flexdiscount-user-discounts fl-affiliate'>" . shopFlexdiscountHelper::getBlock('flexdiscount.affiliate', array(
                        'affiliate' => $user_affiliate
                    )) . "</span>";
        }
    }

    /**
     * Get availible discounts
     * If isset product, then get discounts only to it
     * 
     * @param array|shopProduct $product
     * @param string $view_type - type of display
     * @return string - HTML
     */
    public static function getAvailibleDiscounts($product = null, $view_type = null)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $user_discounts = $user_categories = array();
            $return = $return_tail = '';
            $deny = false;
            $discounts = shopFlexdiscountWorkflow::getDiscounts();
            $contact_id = wa()->getUser()->getId();
            $coupon_info = shopFlexdiscountMask::getCouponInfo();
            // Если информация о купоне отсутствует
            if (!$coupon_info) {
                // Получаем действующий купон
                $coupon_code = wa()->getStorage()->get("flexdiscount-coupon");
                $coupon_info = shopFlexdiscountHelper::couponCheck($coupon_code);
            }

            if ($contact_id) {
                // Получаем категории, к которым принадлежит пользователь
                $wcc = new waContactCategoriesModel();
                $categories = $wcc->getContactCategories($contact_id);
                if ($categories) {
                    foreach ($categories as $c) {
                        $user_categories[$c['id']] = $c['id'];
                    }
                }
            }
            if ($product) {
                $product = ($product instanceof shopProduct) ? $product->getData() : $product;
                $new_product = $product;
                $new_product['type'] = 'product';
                if (!isset($new_product['product'])) {
                    $new_product['product'] = array();
                }
                $new_product['product']['id'] = $new_product['product_id'] = $new_product['id'];
                $new_product['product']['type_id'] = $new_product['type_id'];
            }
            $discount_masks = shopFlexdiscountWorkflow::getDiscountMasks();

            // Если не существует дерева категорий, то создаем его
            shopFlexdiscountMask::getCategoryTree();

            // Отсеиваем скидки, которые не распространяются на товар
            foreach ($discounts as $k => $d) {
                // Проверка принадлежности пользователя к категории контакта
                if ($d['contact_category_id'] && $user_categories) {
                    if (!isset($user_categories[$d['contact_category_id']])) {
                        unset($discounts[$k]);
                        continue;
                    }
                } elseif ($d['contact_category_id'] && !$user_categories) {
                    unset($discounts[$k]);
                    continue;
                }

                if ($d['mask'] == '-') {
                    unset($discounts[$k]);
                    $deny = true;
                    continue;
                }
                // Проверка времени действия скидки
                if (isset($d['expire_datetime']) && strtotime($d['expire_datetime']) < time()) {
                    unset($discounts[$k]);
                    continue;
                }
                // Если скидка закреплена за другим купоном, то прерываем ее обработку
                if ($coupon_info && $d['coupon_id'] && $coupon_info['id'] !== $d['coupon_id']) {
                    unset($discounts[$k]);
                    continue;
                } elseif (!$coupon_info && $d['coupon_id']) {
                    unset($discounts[$k]);
                    continue;
                }

                // Получаем значение масок скидок
                preg_match_all("/\d+/", $d['value'], $matches);
                $mask_value = array();
                // Первое значение маски
                if (!empty($matches[0][0])) {
                    $mask_value[] = $matches[0][0];
                }
                // Второе значение маски
                if (!empty($matches[0][1])) {
                    $mask_value[] = $matches[0][1];
                }
                // Третье значение маски (минимальная цена)
                if (!empty($matches[0][2])) {
                    $mask_value[] = $matches[0][2];
                }
                $discounts[$k]['mask_value'] = $mask_value;
                if ($product) {
                    if (!shopFlexdiscountMask::itemCheck($d, $new_product, shopFlexdiscountWorkflow::getCategories())) {
                        unset($discounts[$k]);
                        continue;
                    }
                }
            }
            // Если после проверок скидки еще остались
            if ($discounts) {
                // Округление скидок
                $settings = shopFlexdiscountHelper::getSettings();
                $round = isset($settings['round']) ? $settings['round'] : 'not';
                if ($product) {

                    // Если у товара не указана закупочная цена
                    if (!isset($product['purchase_price'])) {
                        if (isset($product['skus'][$product['sku_id']]['purchase_price'])) {
                            $product['purchase_price'] = $product['skus'][$product['sku_id']]['purchase_price'];
                        } else {
                            $sku_model = new shopProductSkusModel();
                            $sku = $sku_model->getSku($product['sku_id']);
                            if ($sku) {
                                $product['purchase_price'] = $sku['purchase_price'];
                            }
                        }
                    }

                    $primary_currency = wa('shop')->getConfig()->getCurrency(true);
                    // Вычисляем значение скидок для каждого варианта товара
                    // Если вариантов нету, то мы находимся в каталоге
                    if (!empty($product['skus'])) {
                        foreach ($product['skus'] as $sku_id => $sku) {
                            foreach ($discounts as $k => $d) {
                                $discount = $sku['primary_price'] * $d['discount_percentage'] / 100 + (in_array($d['mask'], $discount_masks) || $d['discounteachitem'] ? $d['discount'] : 0);
                                $product_discount_price = $sku['primary_price'] - $discount;

                                // Ограничение скидки
                                if (!empty($settings['limit_discount']['value'])) {
                                    $limit = !empty($product['purchase_price']) ? $product['purchase_price'] : 0;
                                    if (!empty($settings['limit_discount']['percentage'])) {
                                        $limit += $settings['limit_discount']['price'] == 'purchase' ? $settings['limit_discount']['percentage'] * $limit / 100 : $settings['limit_discount']['percentage'] * $product['price'] / 100;
                                    }
                                    if ($product['price'] - $discount < $limit) {
                                        $discount = $product['price'] - $limit;
                                        if ($discount < 0) {
                                            $discount = 0;
                                            $product_discount_price = $product['price'];
                                        } else {
                                            $product_discount_price = $limit;
                                        }
                                    }
                                }

                                if ($product_discount_price < 0) {
                                    $discount = $product['price'];
                                    $product_discount_price = 0;
                                }

                                $discount = ($round == 'ceil' ? ceil($discount) : ($round == 'floor' ? floor($discount) : ($round == 'tens' ? round($discount, -1) : $discount)));
                                $product_discount_price = ($round == 'ceil' ? ceil($product_discount_price) : ($round == 'floor' ? floor($product_discount_price) : ($round == 'tens' ? round($product_discount_price, -1) : $product_discount_price)));
                                $user_discounts[$sku_id][$d['id']] = array(
                                    'name' => $d['name'] ? $d['name'] : '',
                                    'discount' => shop_currency($discount, $primary_currency, null),
                                    'discount_html' => shop_currency_html($discount, $primary_currency, null),
                                    'price' => shop_currency($product_discount_price, $primary_currency, null),
                                    'price_html' => shop_currency_html($product_discount_price, $primary_currency, null),
                                    'clear_price' => ($product_discount_price > 0) ? shop_currency($product_discount_price, $product['currency'], null, 0) : 0,
                                    'currency' => $product['currency'],
                                    'params' => array(
                                        'discount' => shop_currency($d['discount'], $product['currency'], null),
                                        'discount_html' => shop_currency_html($d['discount'], $product['currency'], null),
                                        'discount_percentage' => $d['discount_percentage'],
                                        'affiliate' => $d['affiliate'],
                                        'value' => $d['mask_value'],
                                        'code' => $d['code'],
                                    )
                                );
                            }
                        }
                    } else {
                        $return .= "<span class='flexdiscount-show-all'>";
                        foreach ($discounts as $k => $d) {
                            $discount = $product['price'] * $d['discount_percentage'] / 100 + (in_array($d['mask'], $discount_masks) ? $d['discount'] : 0);
                            $product_discount_price = $product['price'] - $discount;
                            $discount = ($round == 'ceil' ? ceil($discount) : ($round == 'floor' ? floor($discount) : ($round == 'tens' ? round($discount, -1) : $discount)));
                            $product_discount_price = ($round == 'ceil' ? ceil($product_discount_price) : ($round == 'floor' ? floor($product_discount_price) : ($round == 'tens' ? round($product_discount_price, -1) : $product_discount_price)));
                            $user_discounts[0][$d['id']] = array(
                                'name' => $d['name'] ? $d['name'] : '',
                                'discount' => shop_currency($discount, $primary_currency, null),
                                'discount_html' => shop_currency_html($discount, $primary_currency, null),
                                'price' => shop_currency($product_discount_price, $primary_currency, null),
                                'price_html' => shop_currency_html($product_discount_price, $primary_currency, null),
                                'clear_price' => ($product_discount_price > 0) ? shop_currency($product_discount_price, $product['currency'], null, 0) : 0,
                                'currency' => $product['currency'],
                                'params' => array(
                                    'discount' => shop_currency($d['discount'], $product['currency'], null),
                                    'discount_html' => shop_currency_html($d['discount'], $product['currency'], null),
                                    'discount_percentage' => $d['discount_percentage'],
                                    'affiliate' => $d['affiliate'],
                                    'value' => $d['mask_value'],
                                    'code' => $d['code'],
                                )
                            );
                        }
                        $return_tail .= "</span>";
                    }
                } else {
                    $return .= "<span class='flexdiscount-show-all'>";
                    foreach ($discounts as $k => $d) {
                        $user_discounts[0][$d['id']] = array(
                            'name' => $d['name'] ? $d['name'] : '',
                            'discount' => null,
                            'discount_html' => null,
                            'price' => null,
                            'price_html' => null,
                            'params' => array(
                                'discount' => shop_currency($d['discount'], null, null),
                                'discount_html' => shop_currency_html($d['discount'], null, null),
                                'discount_percentage' => $d['discount_percentage'],
                                'affiliate' => $d['affiliate'],
                                'value' => $d['mask_value'],
                                'code' => $d['code'],
                            )
                        );
                    }
                    $return_tail .= "</span>";
                }
            }
            // Передаем данные в блок
            return $return . shopFlexdiscountHelper::getBlock('flexdiscount.available', array(
                        'discounts' => $user_discounts,
                        'view_type' => $view_type,
                        'deny' => $deny
                    )) . $return_tail;
        }
    }

    /**
     * Get all workflow of discounts for product. Uses for calculating workflow. 
     * 
     * @param array|shopProduct $product
     * @param int $sku_id
     * @param array $params - vars to assign
     * @param int $quantity
     * @param bool $return_html - return HTML or data
     * @return string
     */
    public static function getCurrentDiscount($product, $sku_id = '', $params = array(), $quantity = 1, $return_html = true)
    {
        if (shopDiscounts::isEnabled('flexdiscount') && !empty($product)) {
            $product = ($product instanceof shopProduct) ? $product->getData() : $product;
            // Если передан вариант товара, то заменяем данные
            if ($sku_id) {
                $sku_model = new shopProductSkusModel();
                $sku = $sku_model->getSku($sku_id);
                if ($sku) {
                    $product['sku_id'] = $sku['id'];
                    $product['price'] = $sku['primary_price'];
                }
            }
            // Если у товара не указана закупочная цена
            if (!isset($product['purchase_price'])) {
                if (isset($product['skus'][$product['sku_id']]['purchase_price'])) {
                    $product['purchase_price'] = $product['skus'][$product['sku_id']]['purchase_price'];
                } else {
                    $sku_model = new shopProductSkusModel();
                    $sku = $sku_model->getSku($product['sku_id']);
                    if ($sku) {
                        $product['purchase_price'] = $sku['purchase_price'];
                    }
                }
            }

            //Если этот товар уже обрабатывался, то вернем его данные
            if (!empty(self::$product_workflow[$product['id']][$product['sku_id']])) {
                if ($return_html) {
                    // Передаем данные в блок
                    $params['workflow'] = self::$product_workflow[$product['id']][$product['sku_id']];
                    return shopFlexdiscountHelper::getBlock('flexdiscount.current', $params);
                } else {
                    return self::$product_workflow[$product['id']][$product['sku_id']];
                }
            }

            // Изменяем количество товара
            if ($quantity) {
                $product['quantity'] = $quantity;
            } elseif (empty($product['quantity'])) {
                $product['quantity'] = 1;
            }
            $workflow = array(
                'discount' => 0,
                'affiliate' => 0,
                'items' => array(),
                'product' => $product,
            );
            $discount = $affiliate = 0;
            // Получаем значения скидок для корзины
            $cart_workflow = shopFlexdiscountWorkflow::getCartWorkflow();

            // Цена товара приходит в основной валюте. У товара указана валюта, указанной цены за него
            // Нам необходимо сконвертировать цену в текущую валюту
            // На входе основная валюта - на выходе текущая
            $product = shopFlexdiscountHelper::convertCurrency($product, wa('shop')->getConfig()->getCurrency(true));
            // Имитируем наличие переданного товара в корзине
            $update_workflow = shopFlexdiscountWorkflow::updateWorkflow($product);
            if ($cart_workflow['discount'] < $update_workflow['discount'] || $cart_workflow['affiliate'] < $update_workflow['affiliate']) {
                // Получаем маски, которые начисляют скидки на каждый товар, а не на общую сумму.
                // Необходимо для расчета индивидуальной скидки товара
                $discount_masks = shopFlexdiscountWorkflow::getDiscountMasks();
                $settings = shopFlexdiscountHelper::getSettings();
                // Округление скидок
                $round = isset($settings['round']) ? $settings['round'] : 'not';

                $product_discount = 0;
                $product_discount_price = $product['price'];

                // Выполняем перебор новых скидок
                foreach ($update_workflow['discount_name'] as $k => $discount_name) {
                    $product_discount = !empty($discount_name['skus'][$product['sku_id']]) ? $discount_name['skus'][$product['sku_id']]['discount'] : 0;
                    // Запоминаем маски, чтобы вычислить, какую из них применить в случае совпадения двух и более масок.
                    if (!isset($product['mask'][$discount_name['params']['mask']])) {
                        $product['mask'][$discount_name['params']['mask']] = $product_discount;
                    } else {
                        if (isset($product['mask'][$discount_name['params']['mask']]) && $product['mask'][$discount_name['params']['mask']] < $product_discount) {
                            $product_discount_price = $product_discount_price + $product['mask'][$discount_name['params']['mask']];
                            $product['mask'][$discount_name['params']['mask']] = $product_discount;
                        } else {
                            continue;
                        }
                    }
                    $product_discount = ($round == 'ceil' ? ceil($product_discount) : ($round == 'floor' ? floor($product_discount) : ($round == 'tens' ? round($product_discount, -1) : $product_discount)));

                    // Для корректного отображения "Действующих скидок" ограничиваем скидку для товара
                    if (!empty($settings['limit_discount']['value'])) {
                        $limit = !empty($product['purchase_price']) ? $product['purchase_price'] : 0;
                        if (!empty($settings['limit_discount']['percentage'])) {
                            $limit += $settings['limit_discount']['price'] == 'purchase' ? $settings['limit_discount']['percentage'] * $limit / 100 : $settings['limit_discount']['percentage'] * $product['price'] / 100;
                        }
                        if ($product['price'] - $product_discount < $limit) {
                            $product_discount = $product['price'] - $limit;
                            if ($product_discount < 0) {
                                $product_discount = 0;
                            }
                        }
                    } elseif ($product_discount > $product['price']) {
                        $product_discount = $product['price'];
                    }
                    $workflow['items'][$k] = array(
                        'discount' => $product_discount ? shop_currency($product_discount, $product['currency'], null) : 0,
                        'discount_html' => $product_discount ? shop_currency_html($product_discount, $product['currency'], null) : 0,
                        'clear_discount' => $product_discount,
                        'affiliate' => in_array($discount_name['params']['mask'], $discount_masks) ? $discount_name['params']['affiliate'] : 0,
                        'name' => $discount_name['name'],
                        'params' => $discount_name['params'],
                        'custom_params' => !empty($discount_name['skus'][$product['sku_id']]['custom_params']) ? $discount_name['skus'][$product['sku_id']]['custom_params'] : array()
                    );
                    $product_discount_price -= $product_discount;
                    $discount += $product_discount;
                    $affiliate += $workflow['items'][$k]['affiliate'];
                }

                // Ограничение скидки
                if (!empty($settings['limit_discount']['value'])) {
                    $limit = !empty($product['purchase_price']) ? $product['purchase_price'] : 0;
                    if (!empty($settings['limit_discount']['percentage'])) {
                        $limit += $settings['limit_discount']['price'] == 'purchase' ? $settings['limit_discount']['percentage'] * $limit / 100 : $settings['limit_discount']['percentage'] * $product['price'] / 100;
                    }
                    if ($product['price'] - $discount < $limit) {
                        $discount = $product['price'] - $limit;
                        if ($discount < 0) {
                            $discount = 0;
                            $product_discount_price = $product['price'];
                        } else {
                            $product_discount_price = $limit;
                        }
                    }
                }

                if ($product_discount_price < 0) {
                    $discount = $product['price'];
                    $product_discount_price = 0;
                }

                $workflow['discount'] = shop_currency($discount, $product['currency'], null);
                $workflow['discount_html'] = shop_currency_html($discount, $product['currency'], null);
                $workflow['clear_discount'] = shop_currency($discount, $product['currency'], null, false);
                $workflow['affiliate'] = $affiliate;
                $workflow['currency'] = $product['currency'];
                $workflow['quantity'] = $product['quantity'];
                $workflow['price'] = shop_currency($product_discount_price, $product['currency'], null);
                $workflow['price_html'] = shop_currency_html($product_discount_price, $product['currency'], null);
                $workflow['clear_price'] = shop_currency($product_discount_price, $product['currency'], null, 0);
                $workflow['real_price'] = $product['price'];

                // Запоминаем обработанные скидки для товара
                self::$product_workflow[$product['id']][$product['sku_id']] = $workflow;
            } else {
                $workflow['clear_discount'] = 0;
                $workflow['price'] = shop_currency($product['price'], $product['currency'], null);
                $workflow['price_html'] = shop_currency_html($product['price'], $product['currency'], null);
                $workflow['clear_price'] = shop_currency($product['price'], $product['currency'], null, 0);
            }
            $workflow['deny'] = $cart_workflow['deny'];
            /**
             * Содержимое $workflow
             *   Массив, содержащий информацию о возможных скидках к товару
             *   array(
             *       'discount' => общая скидка для товара,
             *       'discount_html' => общая скидка для товара с символом рубля,
             *       'clear_discount' => чистая скидка без валют,
             *       'affiliate' => количество бонусов,
             *       'currency' => валюта товара,
             *       'quantity' => количество товара,
             *       'price' => цена товара со скидкой,
             *       'price_html' => цена товара со скидкой с символом рубля,
             *       'clear_price' => чистая цена без валют,
             *       'real_price' => цена товара без скидок,
             *       'product' => array(), данные о товаре
             *       'items' => array( значения примененных скидок
             *              'discount' => скидка,
             *              'discount_html' => скидка с символом рубля,
             *              'clear_discount' => чистая скидка без валют,
             *              'custom_params' => array(), разные параметры, передаваемые из модулей скидок
             *              'affiliate' => бонус,
             *              'name' => название скидки,
             *              'params' => array( информация о правиле скидок
             *                  'discount' => абсолютное значение скидки в валюте,
             *                  'discount_percentage' => процентное значение скидки,
             *                  'affiliate' => бонусы,
             *                  'mask' => маска правила скидок, 
             *                  'code' => символьный код скидки,
             *                  'discounteachitem' => использовалось ли условие начисления скидки на каждый товар,
             *              )
             *          ),
             *        'deny' => (bool) Был ли хоть раз применен запрет скидки для товара 
             *       )
             */
            if ($return_html) {
                // Передаем данные в блок
                $params['workflow'] = $workflow;
                return shopFlexdiscountHelper::getBlock('flexdiscount.current', $params);
            } else {
                return $workflow;
            }
        }
    }

    /**
     * Get new product price with discount
     * 
     * @param aray $product
     * @param int $sku_id
     * @param int $html - uses for ruble symbol
     * @return string
     */
    public static function price($product, $sku_id = '', $html = null)
    {
        
        // Если плагин будет отключен, то выведется обычная цена. 
        // Это сделано на случай, если пользователь заменит стандартный вывод цены в шаблоне
        if (!empty($product)) {
            $product = ($product instanceof shopProduct) ? $product->getData() : $product;
            $return = '';
            $return_tail = '';
            if (shopDiscounts::isEnabled('flexdiscount')) {
                $settings = shopFlexdiscountHelper::getSettings();
                if ($html !== null && $html) {
                    $html = 1;
                } elseif ($html === 0) {
                    $html = 0;
                } else {
                    $html = isset($settings['ruble']) && $settings['ruble'] == 'html' ? 1 : 0;
                }

                $return .= "<span class='flexdiscount-price product-id-" . $product['id'] . "'";

                if (!$sku_id) {
                    $sku_id = $product['sku_id'];
                    $return .= " data-sku-id='0'";
                } else {
                    $return .= " data-sku-id='" . $sku_id . "'";
                }
                $return_tail .= "</span>";

                // Если товар уже обрабатывался, то возвращаем его данные
                if (!empty(self::$product_workflow[$product['id']][$sku_id])) {
                    $workflow = self::$product_workflow[$product['id']][$sku_id];
                } else {
                    if (empty($product['quantity'])) {
                        $product['quantity'] = 1;
                    }
                    $workflow = self::getCurrentDiscount($product, $sku_id, array(), $product['quantity'], false);
                }
                if (isset($workflow['price'])) {
                    $return .= " data-price='" . $workflow['clear_price'] . "'>";
                    return $return . ($html ? $workflow['price_html'] : $workflow['price']) . $return_tail;
                }
            }
            if (!empty($sku_id) && !empty($product['skus'][$sku_id])) {
                $price = $product['skus'][$sku_id]['price'];
                $price = $html ? shop_currency_html($product['skus'][$sku_id]['price'], $product['currency'], wa('shop')->getConfig()->getCurrency(false)) : shop_currency($product['skus'][$sku_id]['price'], $product['currency'], wa('shop')->getConfig()->getCurrency(false));
                $return .= ' data-price="' . shop_currency($product['skus'][$sku_id]['price'], $product['currency'], wa('shop')->getConfig()->getCurrency(true), 0) . '"';
            } else {
                $price = $html ? shop_currency_html($product['price'], wa('shop')->getConfig()->getCurrency(true), wa('shop')->getConfig()->getCurrency(false)) : shop_currency($product['price'], wa('shop')->getConfig()->getCurrency(true), wa('shop')->getConfig()->getCurrency(false));
                $return .= ' data-price="' . shop_currency($product['price'], wa('shop')->getConfig()->getCurrency(true), wa('shop')->getConfig()->getCurrency(false), 0) . '"';
            }
            $return .= ">";
            return $return . $price . $return_tail;
        }
        return '';
    }

    /**
     * Get product price with discount on cart page
     * 
     * @param array $product
     * @param bool $mult_quantity - should we show price for all quantity of products or just for single product
     * @param int $html - html format for ruble sign
     * @param bool $format - use currencies or return clear price
     * @param bool $update - use html wrap to update price
     * @return string
     */
    public static function cartPrice($product, $mult_quantity = true, $html = null, $format = true, $update = true)
    {
        // Массив обработанных товаров
        static $cart_products = array();
        // Если плагин будет отключен, то выведется обычная цена. 
        // Это сделано на случай, если пользователь заменит стандартный вывод цены в шаблоне
        if (!empty($product)) {
            $price = shop_currency($product['price'], $product['currency'], null, false);
            $quantity = !empty($product['quantity']) ? $product['quantity'] : 1;
            if (shopDiscounts::isEnabled('flexdiscount')) {
                $settings = shopFlexdiscountHelper::getSettings();
                if ($html !== null && $html) {
                    $html = 1;
                } elseif ($html === 0) {
                    $html = 0;
                } else {
                    $html = isset($settings['ruble']) && $settings['ruble'] == 'html' ? 1 : 0;
                }
                if (!isset($cart_products[$product['sku_id']])) {
                    // Получаем значения скидок для корзины
                    $cart_workflow = shopFlexdiscountWorkflow::getCartWorkflow();
                    // Если имеется скидка
                    if ($cart_workflow['discount']) {
                        // Получаем массив товаров, к которым была применена скидка
                        $flexdiscount_products = shopFlexdiscountWorkflow::getFlexdiscountProducts($cart_workflow['discount_name'], true, true);
                        // Если искомый товар содержится среди этих товаров
                        if (isset($flexdiscount_products[$product['sku_id']])) {
                            // Вычисляем цену со скидкой 
                            $round = isset($settings['round']) ? $settings['round'] : 'not';
                            $flexdiscount_products[$product['sku_id']]['total_discount'] = ($round == 'ceil' ? ceil($flexdiscount_products[$product['sku_id']]['total_discount']) : ($round == 'floor' ? floor($flexdiscount_products[$product['sku_id']]['total_discount']) : ($round == 'tens' ? round($flexdiscount_products[$product['sku_id']]['total_discount'], -1) : $flexdiscount_products[$product['sku_id']]['total_discount'])));
                            $price_with_discount = $price - $flexdiscount_products[$product['sku_id']]['total_discount'] / $quantity;
                            if ($price_with_discount < 0) {
                                $price_with_discount = 0;
                            }
                            // Ограничение скидки
                            if (!empty($settings['limit_discount']['value'])) {
                                $limit = !empty($flexdiscount_products[$product['sku_id']]['purchase_price']) ? $flexdiscount_products[$product['sku_id']]['purchase_price'] : 0;
                                if (!empty($settings['limit_discount']['percentage'])) {
                                    $limit += $settings['limit_discount']['price'] == 'purchase' ? $settings['limit_discount']['percentage'] * $limit / 100 : $settings['limit_discount']['percentage'] * $price_with_discount / 100;
                                }
                                // Если предел больше самой цены товара
                                if ($price < $limit) {
                                    $limit = $price;
                                }
                                if ($price_with_discount < $limit) {
                                    $price_with_discount = $limit;
                                }
                            }
                            // Запоминаем обработанный товар
                            $cart_products[$product['sku_id']] = array(
                                "price" => $price_with_discount,
                                "quantity" => $quantity
                            );
                            $price = $cart_products[$product['sku_id']]['price'];
                            $quantity = $cart_products[$product['sku_id']]['quantity'];
                        }
                    }
                } else {
                    $price = $cart_products[$product['sku_id']]['price'];
                    $quantity = $cart_products[$product['sku_id']]['quantity'];
                }
            }
            $price = $mult_quantity ? $price * $quantity : $price;
            $current = wa('shop')->getConfig()->getCurrency(false);
            $price = $format ? ($html ? shop_currency_html($price, $current) : shop_currency($price, $current)) : shop_currency($price, $current, null, false);
            $return = $update ? '<span class="flexdiscount-cart-price cart-id-' . $product['id'] . '" data-cart-id="' . $product['id'] . '"  data-mult="' . $mult_quantity . '" data-html="' . $html . '" data-format="' . $format . '">' . $price . '</span>' : $price;
            return $return;
        }
    }

}
