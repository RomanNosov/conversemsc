<?php

class shopLostproductsPluginBackendSaveMenuStateController extends waJsonController
{
    public function execute()
    {
        $expanded = waRequest::post('expanded', null, waRequest::TYPE_INT);
        if ($expanded !== null && in_array($expanded, array(0, 1))) {
            $csm = new waContactSettingsModel();
            $csm->set(wa()->getUser()->getId(), 'shop.lostproducts', 'menu_expanded', $expanded);
        }
    }
}
