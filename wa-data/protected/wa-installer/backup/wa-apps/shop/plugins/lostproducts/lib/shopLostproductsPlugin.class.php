<?php

class shopLostproductsPlugin extends shopPlugin
{
    private function getCheckParams()
    {
        static $check_params;
        if (empty($check_params)) {
            $check_params = include $this->path.'/lib/config/check.php';
        }
        return $check_params;
    }

    public static function getSettingsFeatures()
    {
        $fm = new shopFeatureModel();
        $features = $fm->select('*')->where('parent_id IS NULL AND code <> "weight" AND type <> "divider"')->order('name, code')->fetchAll();
        foreach ($features as &$f) {
            $f = array(
                'value'       => $f['id'],
                'title'       => $f['name'],
                'description' => $f['code'],
            );
        }
        unset($f);
        return $features;
    }

    public function backendProducts()
    {
        $this->addJs('js/lostproducts.js');
        $csm = new waContactSettingsModel();
        $expanded = $csm->getOne(wa()->getUser()->getId(), 'shop.lostproducts', 'menu_expanded');
        $expanded = is_numeric($expanded) && in_array($expanded, array(0, 1)) ? $expanded : 1;
        $html = '
        <div class="block">
            <h5 class="heading lostproducts-expand-menu">
                <i class="icon16 '.($expanded ? 'darr' : 'rarr').'"></i>'._wp('Lost products').'
            </h5>
            <ul class="menu-v with-icons lostproducts-actions" style="'.($expanded ? '' : ' display: none;').'">';
        foreach ($this->getCheckParams() as $key => $param) {
            $html .= '<li id="lostproducts-'.$key.'-">
                <span class="count"></span>
                <a href="#/products/hash=lostproducts-'.$key.'">'.$param['name'].'</a>
            </li>';
        }
        $html .= '</ul></div>';
        return array(
            'sidebar_section' => $html,
        );
    }

    public function productsCollection($params)
    {
        $collection = $params['collection'];
        $hash = $collection->getHash();
        if (strpos($hash[0], 'lostproducts-') !== 0) {
            return null;
        }
        $hash_key = preg_replace('/^lostproducts\-/', '', $hash[0]);
        $check_params = $this->getCheckParams();
        if (!in_array($hash_key, array_keys($check_params))) {
            return null;
        }
        $method = 'modifyCollection'.ucfirst($hash_key);
        $this->$method($collection);
        $collection->addTitle($check_params[$hash_key]['title']);
        return true;
    }

    private function modifyCollectionCategory(shopProductsCollection $collection)
    {
        $collection->addWhere("p.category_id IS NULL");
    }

    private function modifyCollectionImages(shopProductsCollection $collection)
    {
        $clauses = array();

        //basic search by database value
        $clauses[] = 'p.image_id IS NULL';

        //find small images by info from database
        if (($limit = (int) $this->getSettings('image_size')) > 0) {
            $clauses[] = "
                (SELECT COUNT(*)
                    FROM shop_product_images pi
                    WHERE pi.product_id = p.id
                        AND (pi.width < $limit OR pi.height < $limit)
                ) > 0
            ";
        }

        //find missing image files
        if ($this->getSettings('images_find_missing_files')) {
            $product_image_model = new shopProductImagesModel();
            if ($product_images = $product_image_model->getAll()) {
                $missing_image_products = array();
                foreach ($product_images as $image) {
                    if (!in_array($image['product_id'], $missing_image_products) && !file_exists(shopImage::getPath($image))) {
                        $missing_image_products[] = $image['product_id'];
                    }
                }
                if ($missing_image_products) {
                    $clauses[] = 'p.id IN('.implode(',', $missing_image_products).')';
                }
            }
        }

        $collection->addWhere(implode(' OR ', $clauses));
    }

    private function modifyCollectionType(shopProductsCollection $collection)
    {
        $collection->addWhere("p.type_id IS NULL");
    }

    private function modifyCollectionTags(shopProductsCollection $collection)
    {
        $collection->addWhere(
            "(SELECT COUNT(*)
            FROM shop_product_tags pt
            WHERE pt.product_id = p.id
            ) < 1"
        );
    }

    private function modifyCollectionDescriptions(shopProductsCollection $collection)
    {
        if ($settings = $this->getSettings('descriptions')) {
            $where = array();
            foreach (array_keys($settings) as $field) {
                $where[] = '(LENGTH(TRIM(p.'.$field.')) < 1 OR p.'.$field.' IS NULL)';
            }
            $collection->addWhere(implode(' OR ', $where));
        } else {
            $collection->addWhere('0');
        }
    }

    private function modifyCollectionMeta(shopProductsCollection $collection)
    {
        if ($settings = $this->getSettings('meta')) {
            $where = array();
            foreach (array_keys($settings) as $field) {
                $where[] = '(LENGTH(TRIM(p.meta_'.$field.')) < 1 OR p.meta_'.$field.' IS NULL)';
            }
            $collection->addWhere(implode(' OR ', $where));
        } else {
            $collection->addWhere('0');
        }
    }

    private function modifyCollectionStock(shopProductsCollection $collection)
    {
        $stock_count_zero_clause = $this->getSettings('stock_count_zero') ? 'OR ps.count < 1' : '';
        $collection->addWhere(
            "(SELECT COUNT(*)
            FROM shop_product_skus ps
            WHERE ps.product_id = p.id
                AND (ps.count IS NULL $stock_count_zero_clause)
            ) > 0"
        );
    }

    private function modifyCollectionWeight(shopProductsCollection $collection)
    {
        $collection->addWhere(
            "(SELECT COUNT(*)
            FROM shop_product_features pf
            LEFT JOIN shop_feature f
                ON f.id = pf.feature_id
            WHERE pf.product_id = p.id
                AND pf.sku_id IS NULL
                AND f.code = 'weight'
            ) < 1"
        );
    }

    private function modifyCollectionFeatures(shopProductsCollection $collection)
    {
        $skipped_features = $this->getSettings('features');
        if ($skipped_features) {
            $feature_ids = implode(',', array_keys($skipped_features));
            $skip_clause = "
                AND (
                    (f.parent_id IS NULL AND f.id NOT IN ($feature_ids))
                        OR (f.parent_id IS NOT NULL AND f.parent_id NOT IN ($feature_ids))
                )
            ";
            $where = "
                (SELECT COUNT(*)
                FROM shop_product_features pf
                LEFT JOIN shop_feature f
                    ON f.id = pf.feature_id
                WHERE pf.product_id = p.id
                    AND pf.sku_id IS NULL
                    AND f.code <> 'weight'
                    AND f.type <> 'divider'
                    $skip_clause
                ) < 1

                AND

                (SELECT COUNT(*)
                FROM shop_type_features tf
                LEFT JOIN shop_feature f
                    ON f.id = tf.feature_id
                WHERE tf.type_id = p.type_id
                    AND f.code <> 'weight'
                    AND f.type <> 'divider'
                    $skip_clause
                ) > 0
            ";
        } else {
            $where = "
                (SELECT COUNT(*)
                FROM shop_product_features pf
                LEFT JOIN shop_feature f
                    ON f.id = pf.feature_id
                WHERE pf.product_id = p.id
                    AND pf.sku_id IS NULL
                    AND f.code <> 'weight'
                    AND f.type <> 'divider'
                ) < 1
            ";
        }
        $collection->addWhere($where);
    }

    private function modifyCollectionPrice(shopProductsCollection $collection)
    {
        if ($this->getSettings('price_find_all_zero_skus')) {
            $clause = "
                (SELECT COUNT(*)
                FROM shop_product_skus ps
                WHERE ps.product_id = p.id
                    AND ps.price <> 0
                ) = 0
            ";
        } else {
            $clause = "
                (SELECT COUNT(*)
                FROM shop_product_skus ps
                WHERE ps.product_id = p.id
                    AND ps.price = 0
                ) > 0
            ";
        }
        $collection->addWhere($clause);
    }

    private function modifyCollectionSkus(shopProductsCollection $collection)
    {
        switch ($this->getSettings('skus')) {
            case 'code_name':
                $clause = '(LENGTH(TRIM(ps.sku)) < 1 OR LENGTH(TRIM(ps.name)) < 1)';
                break;
            case 'name':
                $clause = 'LENGTH(TRIM(ps.name)) < 1';
                break;
            case 'code':
            default:
                $clause = 'LENGTH(TRIM(ps.sku)) < 1';
        }
        $collection->addWhere(
            "(SELECT COUNT(*)
            FROM shop_product_skus ps
            WHERE ps.product_id = p.id
                AND $clause
            ) > 0"
        );
    }

    private function modifyCollectionUnavailable(shopProductsCollection $collection)
    {
        $collection->addWhere(
            "(SELECT COUNT(*)
            FROM shop_product_skus ps
            WHERE ps.product_id = p.id
                AND ps.available <> 1
            ) > 0"
        );
    }

    private function modifyCollectionHidden(shopProductsCollection $collection)
    {
        $collection->addWhere('p.status = 0');
    }

    private function modifyCollectionPages(shopProductsCollection $collection)
    {
        $where = "
            (SELECT COUNT(*)
            FROM shop_product_pages pp
            WHERE pp.product_id = p.id
            ) < 1
        ";
        if ($this->getSettings('pages_find_disabled')) {
            $where .= "
                OR (SELECT COUNT(*)
                FROM shop_product_pages pp
                WHERE pp.product_id = p.id
                    AND pp.status = 0
                ) > 0
            ";
        }
        $collection->addWhere($where);
    }

    private function modifyCollectionReviews(shopProductsCollection $collection)
    {
        $collection->addWhere(
            "(SELECT COUNT(*)
            FROM shop_product_reviews pr
            WHERE pr.product_id = p.id
            ) < 1"
        );
    }

    private function modifyCollectionRelated(shopProductsCollection $collection)
    {
        $template = '
            (p.%s IS NULL
            OR p.%s = 0
            OR (p.%s = 2
                AND (
                    SELECT COUNT(*)
                    FROM shop_product_related pr
                    WHERE pr.product_id = p.id
                        AND pr.type = "%s"
                    ) < 1
                )
            )
        ';
        $types = array('cross_selling', 'upselling');
        $related = $this->getSettings('related');
        if (in_array($related, $types)) {
            $where = str_replace('%s', $related, $template);
        } else {
            $wheres = array();
            foreach ($types as $type) {
                $wheres[] = str_replace('%s', $type, $template);
            }
            $where = implode(' AND ', $wheres);
        }
        $collection->addWhere($where);
    }

    private function modifyCollectionDuplicateNames(shopProductsCollection $collection)
    {
        $model = new waModel();

        $duplicate_names = array_keys($model->query(
            'SELECT name
            FROM shop_product
            GROUP BY name
            HAVING COUNT(*) > 1'
        )->fetchAll('name'));

        $collection->addWhere(count($duplicate_names) ? 'p.name IN ("'.implode('","', $model->escape($duplicate_names)).'")' : '0');
    }

    private function modifyCollectionDuplicateUrls(shopProductsCollection $collection)
    {
        $model = new waModel();

        $duplicate_urls = array_keys($model->query(
            'SELECT url
            FROM shop_product
            GROUP BY url
            HAVING COUNT(*) > 1'
        )->fetchAll('url'));

        $collection->addWhere(count($duplicate_urls) ? 'p.url IN ("'.implode('","', $model->escape($duplicate_urls)).'")' : '0');
    }

    private function modifyCollectionDuplicateSkus(shopProductsCollection $collection)
    {
        $model = new waModel();

        $duplicate_skus = array_keys($model->query(
            'SELECT sku
            FROM shop_product_skus
            WHERE LENGTH(sku) > 0
            GROUP BY sku
            HAVING COUNT(*) > 1'
        )->fetchAll('sku', 1));

        $product_ids = count($duplicate_skus) ? array_keys($model->query(
            'SELECT product_id
            FROM shop_product_skus
            WHERE sku IN(s:skus)',
            array(
                'skus' => $duplicate_skus,
            )
        )->fetchAll('product_id')) : null;

        $collection->addWhere($product_ids ? 'p.id IN ('.implode(',', $product_ids).')' : '0');
    }
}
