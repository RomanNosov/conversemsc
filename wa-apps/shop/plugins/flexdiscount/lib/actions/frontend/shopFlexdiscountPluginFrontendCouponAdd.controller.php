<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginFrontendCouponAddController extends waJsonController
{

    public function execute()
    {
        // Получаем код купона, введенный пользователем
        $post_coupon = waRequest::post("coupon", "", waRequest::TYPE_STRING_TRIM);
        // Осуществляем проверку купона
        if ($coupon = shopFlexdiscountHelper::couponCheck($post_coupon)) {
            // Записываем купон в сессию
            $this->getStorage()->set("flexdiscount-coupon", $coupon['code']);
        }
    }

}