<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginFrontendUpdateDiscountController extends waJsonController
{

    public function execute()
    {
        $product_id = waRequest::post('product_id', '', waRequest::TYPE_INT);
        $sku_id = waRequest::post('sku_id', '', waRequest::TYPE_INT);
        $quantity = waRequest::post('quantity', '1', waRequest::TYPE_INT);
        $view_types = waRequest::post('view_types', array());
        if (!$quantity) {
            $quantity = 1;
        }
        shopFlexdiscountHelper::setImagineQuantity($quantity);
        if ($product_id) {
            // Получаем информацию о товаре
            $product = new shopProduct($product_id);
            if ($product['data']) {
                // Получаем скидку
                $this->response['html'] = shopFlexdiscountPluginHelper::getCurrentDiscount($product, $sku_id, array(), $quantity);
                // Получаем цену со скидкой для вывода на Витрину
                $workflow = shopFlexdiscountPluginHelper::getCurrentDiscount($product, $sku_id, array(), $quantity, false);
                $settings = shopFlexdiscountHelper::getSettings();
                if (isset($settings['ruble']) && $settings['ruble'] == 'html') {
                    $html = 1;
                } else {
                    $html = 0;
                }
                $this->response['discount'] = $workflow['clear_discount'];
                if (!empty($workflow['price'])) {
                    $this->response['price'] = $html ? $workflow['price_html'] : $workflow['price'];
                    $this->response['clear_price'] = $workflow['clear_price'];
                } else {
                    if ($sku_id) {
                        $sku_model = new shopProductSkusModel();
                        $sku = $sku_model->getSku($sku_id);
                        if ($sku) {
                            $price = $html ? shop_currency_html($sku['price'], $product['currency'], wa('shop')->getConfig()->getCurrency(false)) : shop_currency($sku['price'], $product['currency'], wa('shop')->getConfig()->getCurrency(false));
                            $this->response['clear_price'] = shop_currency($sku['price'], $product['currency'], wa('shop')->getConfig()->getCurrency(false), 0);
                        }
                    } else {
                        $price = $html ? shop_currency_html($product['price'], wa('shop')->getConfig()->getCurrency(true), wa('shop')->getConfig()->getCurrency(false)) : shop_currency($product['price'], wa('shop')->getConfig()->getCurrency(true), wa('shop')->getConfig()->getCurrency(false));
                        $this->response['clear_price'] = shop_currency($product['price'], wa('shop')->getConfig()->getCurrency(true), wa('shop')->getConfig()->getCurrency(false), 0);
                    }
                    $this->response['price'] = $price;
                }
                // Генерируем все типы отображения, которые использует пользователь на Витрине
                foreach ($view_types as $view_type) {
                    // Получаем информацию о действующих скидках
                    $this->response['product_discounts'][$view_type] = shopFlexdiscountHelper::getBlock('flexdiscount.product.discounts', array(
                                'workflow' => $workflow,
                                'discounts' => $workflow['items'],
                                'view_type' => $view_type
                    ));
                }
                // Текущие скидки
                $this->response['user_discounts']['discounts'] = shopFlexdiscountPluginHelper::getUserDiscounts();
                // Бонусы
                $this->response['user_discounts']['affiliate'] = shopFlexdiscountPluginHelper::getUserAffiliate();
            }
        }
    }

}
