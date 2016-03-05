<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
try {
     wa('site');
    $site_block_model = new siteBlockModel();
    // Форма для ввода купона
    $block_form = $site_block_model->getById('flexdiscount.affiliate');
    if (!$block_form) {
        $file_form = dirname(__FILE__) . '/../../config/data/flexdiscount.affiliate.html';
        if (file_exists($file_form)) {
            $block_content_form = file_get_contents($file_form);
            $site_block_model->add(array(
                "id" => "flexdiscount.affiliate",
                "content" => $block_content_form,
                "description" => "Начисленные бонусы",
            ));
        }
    }
} catch (Exception $e) {
    
}