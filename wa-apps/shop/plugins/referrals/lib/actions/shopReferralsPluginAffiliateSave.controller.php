<?php

class shopReferralsPluginAffiliateSaveController extends waJsonController
{
    public function execute()
    {
        /**
         * @var shopReferralsPlugin $plugin
         */
        $plugin = wa()->getPlugin('referrals');
        $plugin->saveSettings(waRequest::post());
    }
}