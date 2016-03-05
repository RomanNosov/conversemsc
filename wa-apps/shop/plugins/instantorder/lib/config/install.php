<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
$plugin_id = array('shop', 'instantorder');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');
$app_settings_model->set($plugin_id, 'frontend_product', '1');
$app_settings_model->set($plugin_id, 'title', 'Быстрый заказ');
$app_settings_model->set($plugin_id, 'link_name', 'Быстрый заказ');
$app_settings_model->set($plugin_id, 'width_modal', '320');
$app_settings_model->set($plugin_id, 'height_modal', '240');
$app_settings_model->set($plugin_id, 'successful_order', 'Заказ был успешно создан. Номер Вашего заказа {order_id}. В ближайшее время оператор свяжется с Вами.');
$app_settings_model->set($plugin_id, 'is_captcha', '1');
$app_settings_model->set($plugin_id, 'frontend_cart', '1');
$app_settings_model->set($plugin_id, 'theme', 'base');
$app_settings_model->set($plugin_id, 'resizable', '1');
$app_settings_model->set($plugin_id, 'draggable', '1');
$app_settings_model->set($plugin_id, 'successful_order_js', '');
$app_settings_model->set($plugin_id, 'is_comment', '1');
$app_settings_model->set($plugin_id, 'button_style', 'gradient_0');
$app_settings_model->set($plugin_id, 'css_button_style', '');
$app_settings_model->set($plugin_id, 'without_style', '1');
$app_settings_model->set($plugin_id, 'personal_css', '0');
$app_settings_model->set($plugin_id, 'min_order_sum', '0');
