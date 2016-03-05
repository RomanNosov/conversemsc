<?php

class shopPrettyurlsPluginBackendUpdateurlsController extends waJsonController
{

    public function execute()
    {
        try {
            $mode = waRequest::post('mode', 0, waRequest::TYPE_STRING);
            
            $counter = 0;
    
            $site_url = wa()->getRootUrl(true);
            $shopProduct = new shopProductModel();
            $shop_products = $this->getAllProducts();
            
            foreach ($shop_products as $shop_product) :
                if( ('all' == $mode)
                || (('digits' == $mode) && preg_match('/^\d+$/', $shop_product['url'])) ) {
                    $new_url = $this->genUniqueUrl($shop_product['name'], $shopProduct, $shop_product['id']);
                    $new_url = str_replace('_', '-', $new_url);
                    $new_url = preg_replace('/-+/i', '-', $new_url);
        
                    if ($shop_product['url'] != $new_url) {
                        $shop_product['url'] = $new_url;
                        $shopProduct->updateById($shop_product['id'], $shop_product);
                    }
                }
            endforeach;
            
            $this->response['message'] = _wp('Success');
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

    private function genUniqueUrl($url, $context, $id, &$counter = 0, $length = 512, $field = 'url')
    {
        $counter = 0;
        $url = preg_replace('/\s+/', '-', $url);
        $url = shopHelper::transliterate($url);

        if (strlen($url) == 0) {
            $url = (time() << 24) + $counter++;
        } else {
            $url = mb_substr($url, 0, $length);
        }
        $url = mb_strtolower($url);

        $pattern = mb_substr($context->escape($url, 'like'), 0, $length - 3).'%';
        $sql = "SELECT `{$field}` FROM {$context->getTableName()} WHERE url LIKE '{$pattern}' AND id <> '$id' ORDER BY LENGTH(`{$field}`)";

        $alike = $context->query($sql)->fetchAll('url');

        if (is_array($alike) && isset($alike[$url])) {
            $last = array_shift($alike);
            $counter = 1;
            do {
                $modifier = "-{$counter}";
                $_length = mb_strlen($modifier);
                $url = mb_substr($last['url'], 0, $length - $_length).$modifier;
            } while (isset($alike[$url]) && (++$counter < 100));
            
            if (isset($alike[$url])) {
                $short_uuid = (time() << 24) + $counter++;
                $_length = mb_strlen($short_uuid);

                $url = mb_substr($last['url'], 0, $length - $_length).$short_uuid;
            }
        }

        return mb_strtolower($url);
    }
    
    private function getAllProducts()
    {
        $model = new waModel();
        return $model->query('SELECT * FROM `shop_product`')->fetchAll();
    }
}
