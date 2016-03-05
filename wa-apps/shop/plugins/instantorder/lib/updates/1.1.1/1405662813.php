<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
$plugin_id = array('shop', 'instantorder');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'without_style', '1');
$app_settings_model->set($plugin_id, 'personal_css', '0');


$templates = array(
    'Instantorder' => array(
        'path' => 'plugins/instantorder/templates/Instantorder.html',
        'public' => false,
    ),
    'InstantorderScript' => array(
        'path' => 'plugins/instantorder/js/script.js',
        'public' => true,
    ),
);

foreach ($templates as $id => $template) {
    $template_path = wa()->getDataPath($template['path'], $template['public'], 'shop', true);
    @unlink($template_path);
}

