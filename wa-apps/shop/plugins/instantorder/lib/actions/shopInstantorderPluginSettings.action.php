<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopInstantorderPluginSettingsAction extends waViewAction {

    protected $plugin_id = array('shop', 'instantorder');
    protected $themes = array(
        'base',
        'black-tie',
        'blitzer',
        'cupertino',
        'dark-hive',
        'dot-luv',
        'eggplant',
        'excite-bike',
        'flick',
        'hot-sneaks',
        'humanity',
        'le-frog',
        'mint-choc',
        'overcast',
        'pepper-grinder',
        'redmond',
        'smoothness',
        'south-street',
        'start',
        'sunny',
        'swanky-purse',
        'trontastic',
        'ui-darkness',
        'ui-lightness',
        'vader',
    );
    protected $templates = array(
        'Instantorder' => array(
            'name' => 'Шаблон быстрого заказа',
            'path' => 'plugins/instantorder/templates/Instantorder.html',
            'change_tpl' => false,
            'public' => false,
        ),
        'InstantorderScript' => array(
            'name' => 'шаблон JavaScript кода',
            'path' => 'plugins/instantorder/js/script.js',
            'change_tpl' => false,
            'public' => true,
        ),
    );

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get($this->plugin_id);
        $fields = waContactFields::getAll();
        unset($fields['address']);
        $address = waContactFields::get('address');
        $address = $address->getFields();
        $instantorder_model = new shopInstantorderPluginModel();
        $selected_fields = $instantorder_model->getAll();

        foreach ($this->templates as &$template) {
            $template_path = wa()->getDataPath($template['path'], $template['public'], 'shop', true);
            if (file_exists($template_path)) {
                $template['change_tpl'] = true;
            } else {
                $template_path = wa()->getAppPath($template['path'], 'shop');
            }
            $template['content'] = file_get_contents($template_path);
        }
        unset($template);

        $this->view->assign('themes', $this->themes);
        $this->view->assign('settings', $settings);
        $this->view->assign('fields', $fields);
        $this->view->assign('address_fields', $address);
        $this->view->assign('selected_fields', $selected_fields);
        $this->view->assign('templates', $this->templates);
    }

}
