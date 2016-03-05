<?php

class shopManagerPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        /**
         * @var shopManagerPlugin $plugin
         */
        $plugin = wa()->getPlugin('manager');
        $this->view->assign('settings', $plugin->getSettings());

        if ($plugin->getSettings('admin_only') && !wa()->getUser()->isAdmin('shop')) {
            $this->template = 'SettingsNoaccess';
        }
    }
}