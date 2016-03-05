<?php

class shopReferralsPluginAffiliateEnableController extends waJsonController
{
    public function execute()
    {
        /**
         * @var shopReferralsPlugin $plugin
         */
        $app_settings_model = new waAppSettingsModel();
        if (!waRequest::post('enable')) {
            $app_settings_model->set(array('shop', 'referrals'), 'enabled', 0);
        } else {
            $app_settings_model->del(array('shop', 'referrals'), 'enabled');
        }
    }
}