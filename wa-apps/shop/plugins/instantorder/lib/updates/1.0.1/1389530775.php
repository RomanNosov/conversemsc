<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
$plugin_id = array('shop', 'instantorder');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'frontend_cart', '1');
$app_settings_model->set($plugin_id, 'theme', 'base');
$app_settings_model->set($plugin_id, 'resizable', '1');
$app_settings_model->set($plugin_id, 'draggable', '1');
$app_settings_model->set($plugin_id, 'successful_order_js', '');
$app_settings_model->set($plugin_id, 'is_comment', '1');
