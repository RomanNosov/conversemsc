<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopInstantorderPlugin extends shopPlugin {
   
    public function frontendProduct() {
        if ($this->getSettings('frontend_product')) {
            return array('cart' => self::display());
        }
    }

    public function frontendCart() {
        if ($this->getSettings('frontend_cart')) {
            return self::display();
        }
    }

    public static function display() {
        $app_settings_model = new waAppSettingsModel();
        $html = '';
        if ($app_settings_model->get(array('shop', 'instantorder'), 'status')) {
            if (wa()->getUser()->isAuth()) {
                $contact = wa()->getUser();
                $contact_data = $contact->load();
            }
            $instantorder_model = new shopInstantorderPluginModel();
            $selected_fields = $instantorder_model->getAll();

            if (isset($contact)) {
                foreach ($selected_fields as &$selected_field) {
                    if (preg_match("/address\.(.+)/", $selected_field['type'], $match)) {
                        $field = $match[1];
                        $address = array_pop($contact_data['address']);
                        $selected_field['def_value'] = isset($address['data'][$field]) ? $address['data'][$field] : null;
                        if ($field == 'region') {
                            $region_model = new waRegionModel();
                            $region = $region_model->getByField('code', $selected_field['def_value']);
                            if ($region) {
                                $selected_field['def_value'] = $region['name'];
                            }
                        }
                    } else {
                        $selected_field['def_value'] = $contact->get($selected_field['type'], "default");
                    }
                }
            }
            $country_model = new waCountryModel();
            $countries = $country_model->all();
            $view = wa()->getView();
            $view->assign('settings', $app_settings_model->get(array('shop', 'instantorder')));
            $view->assign('selected_fields', $selected_fields);
            $view->assign('countries', $countries);

            $template_path = wa()->getDataPath('plugins/instantorder/js/script.js', true, 'shop');
            if (file_exists($template_path)) {
                $template_url = wa()->getDataUrl('plugins/instantorder/js/script.js', true, 'shop');
            } else {
                $template_url = wa()->getAppStaticUrl() . 'plugins/instantorder/js/script.js';
            }
            wa()->getResponse()->addJs(substr($template_url, 1));

            $template_path = wa()->getDataPath('plugins/instantorder/templates/Instantorder.html', false, 'shop', true);
            if (!file_exists($template_path)) {
                $template_path = wa()->getAppPath('plugins/instantorder/templates/Instantorder.html', 'shop');
            }
            $html = $view->fetch($template_path);
        }
        return $html;
    }

}
