<?php

class shopPrettyurlsPlugin extends shopPlugin
{
    /**
    * @desc Обрабатывает сохранение товара
    * @param $params array параметры товара
    * @return void
    */
    public function productSave($params)
    {
        if ($this->getSettings('enabled')) :
            // Обработчик вызвается всякий раз при сохранении товара, то есть
            // при сохранении товара пользователем и при импорте товара.
            // Поскольку зацепка "При импорте" не определена, то будем ее эмулировать
            // При пользовательском сохранении всегда передается параметр 'product',
            // Следовательно, если его нет, значит имеем дело с Импортом.
            $data = waRequest::post('product', 0, waRequest::TYPE_ARRAY);
            // Чтобы не менять ссылки при обновлении цен при импорте товаров
            // Обновляются ссылки только у тех товаров, у которых 'url' равен 'id',
            // это позволяет избежать изменения ссылок при обновлении информации
            // об уже существующих товарах (например, цены) при импорте
            $product = $params['data'];
            if (empty($data) && ($product['id'] == $product['url'])) {
                $shopProduct = new shopProductModel();
                $new_url = shopHelper::genUniqueUrl($product['name'], $shopProduct);
                $new_url = str_replace('_', '-', $new_url);
                $new_url = preg_replace('/-+/i', '-', $new_url);
                $product['url'] = $new_url;
                $shopProduct->updateById($product['id'], $product);
            }
        endif;
    }

}