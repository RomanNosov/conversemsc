<?php
return array(
    'hint'                      => array(
        'description'  => '</span><span>
<strong style="position: relative; top: -8px;">Обмен данными с 1С производится в разделе «<a href="?action=importexport#/cml1c/">Импорт/экспорт &rarr; 1С</a>»</strong><br/>
<span class="hint">При обмене будут применены настройки, определенные ниже.</span><br><br>',
        'title'        => 'Обмен данными',
        'control_type' => waHtmlControl::HIDDEN,
    ),
    'price_type'                => array(
        'value'        => 'Розничная',
        'placeholder'  => 'Розничная',
        'title'        => 'Тип цены в 1С',
        'description'  => 'Название типа цены в 1С, по которому будет осуществляться поиск',
        'control_type' => waHtmlControl::INPUT,
    ),
    'price_type_uuid'           => array(
        'value'        => 'cbcf493b-55bc-11d9-848a-00112f43529a',
        'placeholder'  => shopCml1cPlugin::makeUuid(),
        'title'        => 'Идентификатор розничного типа цен в 1С',
        'description'  => 'Будет обновляться автоматически при импорте из 1С',
        'control_type' => waHtmlControl::INPUT,
    ),
    'purchase_price_type'       => array(
        'value'        => 'Закупочная',
        'placeholder'  => 'Закупочная',
        'title'        => 'Тип закупочной цены в 1С',
        'description'  => 'Название типа цены в 1С, по которому будет осуществляться поиск',
        'control_type' => waHtmlControl::INPUT,
    ),
    'purchase_price_type_uuid'  => array(
        'value'        => 'bd72d8fc-55bc-11d9-848a-00112f43529a',
        'placeholder'  => shopCml1cPlugin::makeUuid(),
        'title'        => 'Идентификатор закупочного типа цен в 1С',
        'description'  => 'Будет обновляться автоматически при импорте из 1С',
        'control_type' => waHtmlControl::INPUT,
    ),
    'export_datetime'           => array(
        'control_type' => false,
    ),
    'order_state'               => array(
        'value'            => array(),
        'title'            => 'Статусы заказов',
        'description'      => 'Только заказы в выбранных статусах будут экспортироваться из Shop-Script в 1С.<br/>
Если не выбран ни один статус, то будут экспортироваться заказы во всех статусах.',
        'control_type'     => waHtmlControl::GROUPBOX,
        'options_callback' => array('shopCml1cPlugin', 'controlStatus'),
    ),
    'export_orders'             => array(
        'value'        => 'all',
        'title'        => 'Выгрузка заказов',
        'description'  => 'Выберите, какие заказы нужно экспортировать при автоматическом обмене.<br/>
Если выбран вариант «Новые и измененные», то экспортироваться будут только заказы, созданные или измененные не более чем за 1 час до завершения последнего обмена данными с 1С либо позднее.',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options'      => array(
            'all'     => 'Все',
            'changed' => 'Новые и измененные',
        ),
    ),
    'update_product_fields'     => array(
        'value'            => array(),
        'title'            => 'Обновлять свойства товаров',
        'description'      => 'При синхронизации значений выбранных свойств товаров в Shop-Script будут полностью перезаписаны значениями из 1С для уже <b>существующих</b> товаров.',
        'control_type'     => waHtmlControl::GROUPBOX,
        'options_callback' => array('shopCml1cPlugin', 'controlProductFields'),
        'group'            => 'Товары',
    ),
    'update_product_categories' => array(
        'value'        => 'update',
        'title'        => 'Категории товаров',
        'description'  => 'Обновление данных о принадлежности к категориям при импорте товаров из 1С в Shop-Script',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options'      => array(
            'skip'   => 'Импорт категорий и информации о принадлежности к ним будет пропущен.',
            'none'   => 'Только для новых товаров',
            'update' => 'Только добавлять товар в новые категории',
            'sync'   => 'Добавлять в новые и удалять из устаревших',
        ),
        'group'        => 'Товары',
    ),
    'update_product_types'      => array(
        'value'        => 'update',
        'title'        => 'Тип товаров',
        'description'  => 'Обновление данных о принадлежности к типам при импорте товаров из 1С в Shop-Script.
        Добавляемые характеристики будут соотнесены к типам товаров, для которых они были заданы.',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options'      => array(
            'skip'   => 'Типы товаров и информация о принадлежности товаров к ним не будет импортироваться. Все новые товары будут отнесены к типу по умолчанию (см. настройку ниже).',
            'none'   => 'Только для новых товаров, если указанный тип существует. В противном случае для них будет использоваться тип товаров по умолчанию.',
            'update' => 'Только для новых товаров. Если указанный тип товаров не существует, он будет создан.',
            'sync'   => 'Обновлять для всех товаров.',
        ),
        'group'        => 'Товары',
    ),
    'product_type'              => array(
        'value'            => 1,
        'title'            => 'Тип товаров по умолчанию',
        'description'      => 'Тип товаров в Shop-Script, к которому по умолчанию будут отнесены новые товары из 1С<br/>
Соответствие типа товара определяется на основе параметров "вид номенклатуры" или "вид товара"',
        'control_type'     => waHtmlControl::SELECT,
        'options_callback' => array('shopCml1cPlugin', 'controlType'),
        'group'            => 'Товары',
    ),
    'description_is_html'       => array(
        'value'        => true,
        'title'        => 'Описание товара в HTML',
        'description'  => 'Включить поддержку HTML-тегов в описаниях товаров',
        'control_type' => waHtmlControl::CHECKBOX,
        'group'        => 'Товары',
    ),
    'update_options'            => array(),
    'update_category_fields'    => array(
        'value'        => array(
            'description' => true,
            'name'        => true,
        ),
        'title'        => 'Обновлять свойства категорий',
        'description'  => 'При синхронизации значения выбранных свойств категорий в Shop-Script будут полностью перезаписаны информацией из 1С для <b>существующих</b> категорий',
        'control_type' => waHtmlControl::GROUPBOX,
        'options'      => array(
            'description' => 'Описание',
            'name'        => 'Название',
            'parent_id'   => 'Родительскую категорию',
        ),
    ),
    'base_unit'                 => array(
        'value'        => '',
        'placeholder'  => '%feature_code%',
        'title'        => 'Единица измерения',
        'description'  => 'Значение единицы хранения будет импортировано в качестве характеристики товара с указанным кодом',
        'control_type' => waHtmlControl::INPUT,
        'group'        => 'Товары',
    ),
    'weight_unit'               => array(
        'value'            => 'kg',
        'title'            => 'Единица измерения веса',
        'description'      => 'Укажите размерность хранения веса в 1С',
        'control_type'     => waHtmlControl::SELECT,
        'group'            => 'Товары',
        'options_callback' => array('shopCml1cPlugin', 'controlWeightUnits'),
    ),
    'contact_phone'             => array(
        'value'            => 'phone',
        'title'            => 'Телефон',
        'description'      => 'Укажите поле контакта, экспортируемого из Shop-Script в 1С как номер телефона клиента',
        'control_type'     => waHtmlControl::SELECT,
        'options_callback' => array('shopCml1cPlugin', 'controlCustomerFields'),
    ),
    'contact_email'             => array(
        'value'            => 'email',
        'title'            => 'Email',
        'description'      => 'Выберите свойство контакта, соответствующее email-адресу клиента в Shop-Script',
        'control_type'     => waHtmlControl::SELECT,
        'options_callback' => array('shopCml1cPlugin', 'controlCustomerFields'),
    ),
    'currency'                  => array(
        'value'            => 'RUB',
        'title'            => 'Валюта',
        'description'      => 'Выберите основную (национальную) валюту расчета в 1С.<br/>
        <span class="bold">ВАЖНО: В случае различия валют в 1С и Shop-Script производится пересчет по курсу , установленному в настройках Shop-Script на момент выгрузки.</span>',
        'control_type'     => waHtmlControl::SELECT,
        'options_callback' => array('shopCml1cPlugin', 'controlCurrencies'),
    ),
    'currency_map'              => array(
        'value'        => 'руб',
        'placeholder'  => 'руб',
        'title'        => 'Код валюты',
        'description'  => 'Укажите «Наименование» национальной валюты расчета, указанное в настройках 1С.<br/>
Если необходимо сопоставлять валюты по их ISO-кодам, оставьте это поле пустым.',
        'control_type' => waHtmlControl::INPUT,
    ),
    'stock'                     => array(
        'value'            => 0,
        'title'            => 'Склад',
        'description'      => 'Выберите склад Shop-Script для синхронизации остатков с 1С',
        'control_type'     => waHtmlControl::SELECT,
        'options_callback' => array('shopCml1cPlugin', 'controlStock'),
    ),
);
