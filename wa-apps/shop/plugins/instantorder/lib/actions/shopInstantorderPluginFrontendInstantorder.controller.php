<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopInstantorderPluginFrontendInstantorderController extends waJsonController {

    protected $plugin_id = array('shop', 'instantorder');

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $fields = waRequest::post('fields', array());
            $comment = waRequest::post('comment');
            $product_id = waRequest::post('product_id');
            $sku_id = waRequest::post('sku_id');


            if ($app_settings_model->get($this->plugin_id, 'is_captcha') && !wa()->getCaptcha()->isValid()) {
                $this->errors = _w('Invalid captcha code');
                return false;
            }

            if (wa()->getUser()->isAuth()) {
                $contact = wa()->getUser();
            } else {
                $contact = new waContact();
            }

            $instantorder_model = new shopInstantorderPluginModel();
            $selected_fields = $instantorder_model->getAll();
            $address = array();
            foreach ($selected_fields as &$selected_field) {
                $val = isset($fields[$selected_field['type']]) ? $fields[$selected_field['type']] : null;
                if ($val) {
                    if (preg_match("/address\.(.+)/", $selected_field['type'], $match)) {
                        $address[$match[1]] = $val;
                    } else {
                        $contact->set($selected_field['type'], $val);
                    }
                }
            }

            if ($address) {
                $contact->set('address.shipping', $address);
                $contact->set('address.billing', $address);
            }

            if ($product_id || $sku_id) {
                $this->addToCart();
            }

            $min_order_sum = $app_settings_model->get($this->plugin_id, 'min_order_sum');
            $cart = new shopCart();
            $def_currency = wa('shop')->getConfig()->getCurrency(true);
            $cur_currency = wa('shop')->getConfig()->getCurrency(false);
            $total = $cart->total(true);
            $total = shop_currency($total, $cur_currency, $def_currency, false);

            $min_order_sum = shop_currency($min_order_sum, null, null, false);

            if ($total < $min_order_sum) {
                $this->errors = _w('Вы не можете оформить заказ. Сумма Вашего заказа: ' . shop_currency($total) .
                        ' Минимальная сумма заказа составляет: ' . shop_currency($min_order_sum));
                return false;
            }

            $order_id = $this->createOrder($contact, $comment);
            $successful_order = $app_settings_model->get($this->plugin_id, 'successful_order');
            $successful_order_js = $app_settings_model->get($this->plugin_id, 'successful_order_js');
            $successful_order = str_replace('{order_id}', shopHelper::encodeOrderId($order_id), $successful_order);
            $this->response['message'] = $successful_order . '<script>' . $successful_order_js . '</script>';
        } catch (Exception $ex) {
            $this->errors = $ex->getMessage();
        }
    }

    protected function createOrder($contact, $comment = '') {
        $cart = new shopCart();
        $items = $cart->items(false);
        if (!$items) {
            throw new waException("Нет доступного товара для заказа");
        }
        if (!$cart->total(false)) {
            throw new waException("Неверная сумма заказа");
        }

        // remove id from item
        foreach ($items as &$item) {
            unset($item['id']);
            unset($item['parent_id']);
        }
        unset($item);

        $order = array(
            'contact' => $contact,
            'items' => $items,
            'total' => $cart->total(false),
            'params' => array(),
        );

        $order['discount'] = shopDiscounts::apply($order);

        $routing_url = wa()->getRouting()->getRootUrl();
        $order['params']['storefront'] = wa()->getConfig()->getDomain() . ($routing_url ? '/' . $routing_url : '');

        $order['params']['ip'] = waRequest::getIp();
        $order['params']['user_agent'] = waRequest::getUserAgent();

        foreach (array('shipping', 'billing') as $ext) {
            $address = $contact->getFirst('address.' . $ext);
            if (!$address) {
                $address = $contact->getFirst('address');
            }
            if ($address) {
                foreach ($address['data'] as $k => $v) {
                    $order['params'][$ext . '_address.' . $k] = $v;
                }
            }
        }

        $order['shipping'] = 0;

        $order['comment'] = 'Заказ создан через форму "Быстрый заказ"';
        if ($comment) {
            $order['comment'] .= "\r\nКомментарий покупателя: " . $comment;
        }

        $workflow = new shopWorkflow();
        if ($order_id = $workflow->getActionById('create')->run($order)) {
            $cart->clear();
            return $order_id;
        }
    }

    protected function addToCart() {
        $cart_model = new shopCartItemsModel();
        $code = waRequest::cookie('shop_cart');
        if (!$code) {
            $code = md5(uniqid(time(), true));
            // header for IE
            wa()->getResponse()->addHeader('P3P', 'CP="NOI ADM DEV COM NAV OUR STP"');
            // set cart cookie
            wa()->getResponse()->setCookie('shop_cart', $code, time() + 30 * 86400, null, '', false, true);
        }

        $data = waRequest::post();

        if (isset($data['parent_id'])) {
            $parent = $cart_model->getById($data['parent_id']);
            unset($parent['id']);
            $parent['parent_id'] = $data['parent_id'];
            $parent['type'] = 'service';
            $parent['service_id'] = $data['service_id'];
            if (isset($data['service_variant_id'])) {
                $parent['service_variant_id'] = $data['service_variant_id'];
            } else {
                $service_model = new shopServiceModel();
                $service = $service_model->getById($data['service_id']);
                $parent['service_variant_id'] = $service['variant_id'];
            }
            $cart = new shopCart($code);

            $id = $cart->addItem($parent);
            $total = $cart->total();
            $discount = $cart->discount();
            return;
        }

        $sku_model = new shopProductSkusModel();
        $product_model = new shopProductModel();
        if (!isset($data['product_id'])) {
            $sku = $sku_model->getById($data['sku_id']);
            $product = $product_model->getById($sku['product_id']);
        } else {
            $product = $product_model->getById($data['product_id']);
            if (isset($data['sku_id'])) {
                $sku = $sku_model->getById($data['sku_id']);
            } else {
                if (isset($data['features'])) {
                    $product_features_model = new shopProductFeaturesModel();
                    $sku_id = $product_features_model->getSkuByFeatures($product['id'], $data['features']);
                    if ($sku_id) {
                        $sku = $sku_model->getById($sku_id);
                    } else {
                        $sku = null;
                    }
                } else {
                    $sku = $sku_model->getById($product['sku_id']);
                    if (!$sku['available']) {
                        $sku = $sku_model->getByField(array('product_id' => $product['id'], 'available' => 1));
                    }

                    if (!$sku) {
                        throw new waException(_w('This product is not available for purchase'));
                        return;
                    }
                }
            }
        }

        $quantity = waRequest::post('quantity', 1);

        if ($product && $sku) {
            // check quantity
            if (!wa()->getSetting('ignore_stock_count')) {
                $c = $cart_model->countSku($code, $sku['id']);
                if ($sku['count'] !== null && $c + $quantity > $sku['count']) {
                    $quantity = $sku['count'] - $c;
                    $name = $product['name'] . ($sku['name'] ? ' (' . $sku['name'] . ')' : '');
                    if (!$quantity) {
                        throw new waException(sprintf(_w('Only %d pcs of %s are available, and you already have all of them in your shopping cart.'), $sku['count'], $name));
                        return;
                    } else {
                        throw new waException(sprintf(_w('Only %d pcs of %s are available, and you already have all of them in your shopping cart.'), $sku['count'], $name));
                        return;
                    }
                }
            }
            $services = waRequest::post('services', array());
            if ($services) {
                $variants = waRequest::post('service_variant');
                $temp = array();
                $service_ids = array();
                foreach ($services as $service_id) {
                    if (isset($variants[$service_id])) {
                        $temp[$service_id] = $variants[$service_id];
                    } else {
                        $service_ids[] = $service_id;
                    }
                }
                if ($service_ids) {
                    $service_model = new shopServiceModel();
                    $temp_services = $service_model->getById($service_ids);
                    foreach ($temp_services as $row) {
                        $temp[$row['id']] = $row['variant_id'];
                    }
                }
                $services = $temp;
            }
            $item_id = null;
            $item = $cart_model->getItemByProductAndServices($code, $product['id'], $sku['id'], $services);
            if ($item) {
                $item_id = $item['id'];
                $cart_model->updateById($item_id, array('quantity' => $item['quantity'] + $quantity));
                if ($services) {
                    $cart_model->updateByField('parent_id', $item_id, array('quantity' => $item['quantity'] + $quantity));
                }
            }
            if (!$item_id) {
                $data = array(
                    'code' => $code,
                    'contact_id' => $this->getUser()->getId(),
                    'product_id' => $product['id'],
                    'sku_id' => $sku['id'],
                    'create_datetime' => date('Y-m-d H:i:s'),
                    'quantity' => $quantity
                );
                $item_id = $cart_model->insert($data + array('type' => 'product'));
                if ($services) {
                    foreach ($services as $service_id => $variant_id) {
                        $data_service = array(
                            'service_id' => $service_id,
                            'service_variant_id' => $variant_id,
                            'type' => 'service',
                            'parent_id' => $item_id
                        );
                        $cart_model->insert($data + $data_service);
                    }
                }
            }
            // update shop cart session data
            $shop_cart = new shopCart($code);
            wa()->getStorage()->remove('shop/cart');
            $total = $shop_cart->total();
        } else {
            throw new waException('product not found');
        }
    }

}
