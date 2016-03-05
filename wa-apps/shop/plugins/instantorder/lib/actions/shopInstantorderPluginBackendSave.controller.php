<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopInstantorderPluginBackendSaveController extends waJsonController {

    protected $templates = array(
        'Instantorder' => array(
            'path' => 'plugins/instantorder/templates/Instantorder.html',
            'public' => false,
        ),
        'InstantorderScript' => array(
            'path' => 'plugins/instantorder/js/script.js',
            'public' => true,
        ),
    );

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $shop_pointsissue = waRequest::post('shop_instantorder');
            foreach ($shop_pointsissue as $name => $val) {
                $app_settings_model->set(array('shop', 'instantorder'), $name, $val);
            }

            $field_names = waRequest::post('field_names');
            $field_vals = waRequest::post('field_vals');
            $field_required = waRequest::post('field_required');

            $instantorder_model = new shopInstantorderPluginModel();
            $instantorder_model->truncate();
            if ($field_names) {
                foreach ($field_names as $id => $field_name) {
                    $data = array('name' => $field_name, 'type' => $field_vals[$id], 'required' => $field_required[$id]);
                    $instantorder_model->insert($data);
                }
            }

            $post_templates = waRequest::post('templates');
            $reset_tpls = waRequest::post('reset_tpls');

            foreach ($this->templates as $id => $template) {
                if (isset($reset_tpls[$id])) {
                    $template_path = wa()->getDataPath($template['path'], $template['public'], 'shop', true);
                    @unlink($template_path);
                } else {

                    if (!isset($post_templates[$id])) {
                        throw new waException('Не определён шаблон');
                    }
                    $post_template = $post_templates[$id];

                    $template_path = wa()->getDataPath($template['path'], false, 'shop', true);
                    if (!file_exists($template_path)) {
                        $template_path = wa()->getAppPath($template['path'], 'shop');
                    }

                    $template_content = file_get_contents($template_path);
                    if ($template_content != $post_template) {
                        $template_path = wa()->getDataPath($template['path'], $template['public'], 'shop', true);

                        $f = fopen($template_path, 'w');
                        if (!$f) {
                            throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
                        }
                        fwrite($f, $post_template);
                        fclose($f);
                    }
                }
            }
            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
