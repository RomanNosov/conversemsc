<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginCouponsPrepareDeleteAction extends waViewAction
{

    public function execute()
    {
        $id = waRequest::get("coupon", "", waRequest::TYPE_INT);
        $count = 0;
        if ($id) {
            $sm = new shopFlexdiscountPluginModel();
            // Подсчитываем количество скидок, которые привязаны к купону
            $count = $sm->countByField("coupon_id", $id);
        }
        $this->view->assign("count", $count);
    }

}