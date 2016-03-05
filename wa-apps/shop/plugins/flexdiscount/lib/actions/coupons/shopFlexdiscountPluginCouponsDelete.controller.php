<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginCouponsDeleteController extends waJsonController
{

    public function execute()
    {
       if (waRequest::method() == 'post') {
           $id = waRequest::post("coupon", "", waRequest::TYPE_INT);
           if ($id) {
               $scm = new shopFlexdiscountCouponPluginModel();
               if (!$scm->delete($id)) {
                   $this->errors = "Удаление не удалось";
               }
           }
       }
    }

}