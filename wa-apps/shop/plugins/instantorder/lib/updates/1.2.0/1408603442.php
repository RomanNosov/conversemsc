<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
$plugin_id = array('shop', 'instantorder');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'min_order_sum', '0');
