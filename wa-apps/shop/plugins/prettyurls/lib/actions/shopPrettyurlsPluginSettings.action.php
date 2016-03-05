<?php

class shopPrettyurlsPluginSettingsAction extends waViewAction
{

    public function execute()
    {
        $plugin = wa()->getPlugin('prettyurls');
        
        $apache_ruls = wa()->getDataPath('plugins/prettyurls', true, 'shop').'/apache_ruls.txt';
        if (file_exists($apache_ruls)) {
            $apache_ruls = wa()->getDataUrl('plugins/prettyurls', true, 'shop').'/apache_ruls.txt';
        } else {
            $apache_ruls = false;
        }
        
        $ngnix_ruls = wa()->getDataPath('plugins/prettyurls', true, 'shop').'/ngnix_ruls.txt';
        if (file_exists($ngnix_ruls)) {
            $ngnix_ruls = wa()->getDataUrl('plugins/prettyurls', true, 'shop').'/ngnix_ruls.txt';
        } else {
            $ngnix_ruls = false;
        }
        
        $settings = array(
            'enabled' => (int) $plugin->getSettings('enabled'),
            'apache_rulls' => $apache_ruls,
            'ngnix_rulls' => $ngnix_ruls,
            'plugin_url' => wa()->getAppStaticUrl('shop').'/plugins/prettyurls',
        );
        $this->view->assign('settings', $settings);
    }
}