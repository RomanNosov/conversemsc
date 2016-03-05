<?php

class shopPrettyurlsPluginBackendSaveController extends waJsonController
{

    public function execute()
    {
        try {
            $plugin_settings = waRequest::post('settings', 0, waRequest::TYPE_ARRAY);
            $plugin = wa()->getPlugin('prettyurls');
            $plugin->saveSettings(array(
                'enabled' => $plugin_settings['enabled'],
            ));
            $this->response['message'] = _wp('Saved');
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
