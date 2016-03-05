<?php

return array(
    // 'test_mode' => array(
    //     'value'        => false,
    //     'title'        => /*_wp*/('Test environment'),
    //     'description'  => /*_wp*/('Enable to run Axiomus module in test environment. Disable when moving to production.'),
    //     'control_type' => waHtmlControl::CHECKBOX,
    // ),
    // 'uid' => array(
    //     'value' => '',
    //     'title' => /*_wp*/('Axiomus uid'),
    //     'description' => /*_wp*/('ваш личный uid можете получить axiomus.ru '),
    //     'control_type' => waHtmlControl::INPUT
    // ),
    // 'ukey' => array(
    //     'value' => '',
    //     'title' => /*_wp*/('Axiomus ukey'),
    //     'description' => /*_wp*/('ваш личный ukey можете получить axiomus.ru '),
    //     'control_type' => waHtmlControl::INPUT
    // ),
    'msc1' => array(
        'value' => 300,
        'title' => "Москва - курьер, в пределах МКАД",
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'msc1cl' => array(
        'value' => 300,
        'title' => "Москва - курьер, в пределах МКАД",
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'msc2' => array(
        'value' => 300,
        'title' => 'Москва - курьер, за МКАД',
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'msc2cl' => array(
        'value' => 300,
        'title' => 'Москва - курьер, за МКАД',
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'msc2km' => array(
        'value' => 30,
        'title' => 'Москва - курьер, за МКАД',
        'description' => 'Стоимость километра наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'msc2kmcl' => array(
        'value' => 30,
        'title' => 'Москва - курьер, за МКАД',
        'description' => 'Стоимость километра безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'msc3' => array(
        'value' => 0,
        'title' => 'Москва - самовывоз',
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'msc3cl' => array(
        'value' => 0,
        'title' => 'Москва - самовывоз',
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'spb1' => array(
        'value' => 300,
        'title' => 'СПб - курьер',
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'spb1cl' => array(
        'value' => 300,
        'title' => 'СПб - курьер',
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'spb2' => array(
        'value' => 200,
        'title' => 'СПб - самовывоз',
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'spb2cl' => array(
        'value' => 200,
        'title' => 'СПб - самовывоз',
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'post' => array(
        'value' => 400,
        'title' => 'Почта',
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'postcl' => array(
        'value' => 400,
        'title' => 'Почта',
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'regpick' => array(
        'value' => 300,
        'title' => 'Регионы - самовывоз',
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'regpickcl' => array(
        'value' => 300,
        'title' => 'Регионы - самовывоз',
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'regcour' => array(
        'value' => 300,
        'title' => 'Регионы - курьер',
        'description' => 'Стоимость доставки наличным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    'regcourcl' => array(
        'value' => 300,
        'title' => 'Регионы - курьер',
        'description' => 'Стоимость доставки безналичным расчетом',
        'control_type' => waHtmlControl::INPUT
    ),
    
);
//EOF
