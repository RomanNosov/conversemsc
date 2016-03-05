<?php

return array(
    'hint' => array(
        'title' => '',
        'description'  => '</span><strong>'._wp('Select product properties listed below which you need to control.').'</strong><span>',
        'control_type' => waHtmlControl::HIDDEN,
    ),
    'descriptions' => array(
        'title'        => _wp('Descriptions'),
        'description'  => _wp('Select parts of product description which must be checked'),
        'control_type' => waHtmlControl::GROUPBOX,
        'options' => array(
            array(
                'value' => 'summary',
                'title' => _wp('Product summary'),
            ),
            array(
                'value' => 'description',
                'title' => _wp('Product description'),
            ),
        ),
    ),
    'meta' => array(
        'title'        => _wp('META data'),
        'description'  => _wp('Select parts of product META data which must be checked'),
        'control_type' => waHtmlControl::GROUPBOX,
        'options' => array(
            array(
                'value' => 'title',
                'title' => _wp('TITLE'),
            ),
            array(
                'value' => 'keywords',
                'title' => _wp('META keywords'),
            ),
            array(
                'value' => 'description',
                'title' => _wp('META description'),
            ),
        ),
    ),
    'skus' => array(
        'title'        => _wp('SKUs'),
        'description'  => _wp('Select SKU data which you need to control'),
        'control_type' => waHtmlControl::RADIOGROUP,
        'options'      => array(
            array(
                'value' => 'code_name',
                'title' => _wp('SKU code and name'),
            ),
            array(
                'value' => 'code',
                'title' => _wp('only SKU code'),
            ),
            array(
                'value' => 'name',
                'title' => _wp('only SKU name'),
            ),
        ),
        'value' => 'code',
    ),
    'stock_count_zero' => array(
        'title'        => _wp('Consider zero or negative stock count as missing stock value'),
        'control_type' => waHtmlControl::CHECKBOX,
        'description'  => sprintf(_wp('For option “%s”'), _wp('no stock info')),
    ),
    'price_find_all_zero_skus' => array(
        'title'        => _wp('Find products with all SKUs having zero price'),
        'control_type' => waHtmlControl::CHECKBOX,
        'description'  => sprintf(
            _wp('Enable this option if you want to use filter “%s” to find products where <strong>absolutely no SKU has a price value</strong>.'),
            _wp('no price')
        ).'<br>'._wp('If it’s disabled, all products with at least one empty (zero) SKU price value will be found.'),
    ),
    'image_size' => array(
        'title'        => _wp('Minimum dimension limit for original product images'),
        'description'  => sprintf(
            _wp('If at least one of product’s <strong>original</strong> images’ dimensions is below this value (in pixels), the product will be displayed by filter “%s”.'),
            _wp('no images')
        ).'<br>'._wp('If no positive integer or a non-integer value is specified, then only <strong>products without any images will be found</strong>.'),
        'control_type' => waHtmlControl::INPUT,
        'placeholder'  => _wp('e.g., 1000'),
    ),
    'images_find_missing_files' => array(
        'title'        => _wp('Find missing product image files'),
        'control_type' => waHtmlControl::CHECKBOX,
        'description'  => '<strong>'._wp('This option may slow down the product search.').'</strong>'
    .'<br>'. sprintf(
        _wp('If disabled, filter "%s" will find only products with information about images missing in the database, which is faster and is sufficient in most cases.'),
        _wp('no images')
    )),
    'related' => array(
        'title'        => _wd('shop', 'Related products'),
        'description'  => _wp('Select type of related items which you need to control for your products'),
        'control_type' => waHtmlControl::RADIOGROUP,
        'options'      => array(
            array(
                'value' => 'cross_selling',
                'title' => _wd('shop', 'Cross-selling'),
            ),
            array(
                'value' => 'upselling',
                'title' => _wd('shop', 'Upselling & Similar'),
            ),
            array(
                'value' => 'all',
                'title' => _wp('Any related products'),
            ),
        ),
        'value' => 'all',
    ),
    'pages_find_disabled' => array(
        'title'        => _wp('Find products without info pages'),
        'description'  => sprintf(_wp('Find products with disabled info pages using filter “%s”'), _wp('no pages')),
        'control_type' => waHtmlControl::CHECKBOX,
    ),
    'features' => array(
        'title'            => _wp('Skipped product features'),
        'description'      => sprintf(_wp('Select product features which must not be checked'), _wp('no weight')),
        'control_type'     => waHtmlControl::GROUPBOX,
        'options_callback' => array('shopLostproductsPlugin', 'getSettingsFeatures'),
    ),
);
