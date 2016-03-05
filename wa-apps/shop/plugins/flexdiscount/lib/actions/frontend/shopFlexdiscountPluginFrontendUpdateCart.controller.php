<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginFrontendUpdateCartController extends waJsonController
{

    public function execute()
    {
        $fields = waRequest::post('fields', array());

        if ($fields) {
            $order = shopFlexdiscountWorkflow::getOrder();
            if (!empty($order['items'])) {
                $c = 0;
                foreach ($order['items'] as $item) {
                    if (isset($fields[$item['id']])) {
                        foreach ($fields[$item['id']] as $k => $i) {
                            $this->response['fields'][$c]['removeClass'] = $k;
                            $this->response['fields'][$c]['elem'] = ".flexdiscount-cart-price.cart-id-" . $item['id'] . "." . $k;
                            $this->response['fields'][$c]['price'] = shopFlexdiscountPluginHelper::cartPrice($item, (int) $i['mult'], (int) $i['html'], (int) $i['format']);
                            $c++;
                        }
                    }
                }
            }
        }
        // Текущие скидки
        $this->response['user_discounts']['discounts'] = shopFlexdiscountPluginHelper::getUserDiscounts();
        // Бонусы
        $this->response['user_discounts']['affiliate'] = shopFlexdiscountPluginHelper::getUserAffiliate();
    }

}
