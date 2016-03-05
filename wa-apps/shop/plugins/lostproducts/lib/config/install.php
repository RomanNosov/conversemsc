<?php

$default_settings = array(
    'descriptions' => array(
        'summary',
        'description',
    ),
    'meta' => array(
        'title',
        'keywords',
        'description',
    ),
);
foreach ($default_settings as &$setting) {
    $setting = array_flip($setting);
    foreach ($setting as &$item) {
        $item = 1;
    }
    $setting = json_encode($setting);
}
unset($setting);
$asm = new waAppSettingsModel();
foreach ($default_settings as $key => $value) {
    $asm->set('shop.lostproducts', $key, $value);
}
