<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountHelper
{

    private static $imagine_quantity = 0;

    /**
     * Convert currencies
     * 
     * @param array $products
     * @param string $in_currency
     * @param string $out_currency
     * @return array
     */
    public static function convertCurrency($products, $in_currency = null, $out_currency = null)
    {
        if ($products) {
            if (!$out_currency) {
                $out_currency = wa('shop')->getConfig()->getCurrency(false);
            }
            if (is_array($products) && !isset($products['price'])) {
                foreach ($products as $k => $p) {
                    $products[$k]['price'] = (float) shop_currency($p['price'], $in_currency ? $in_currency : $p['currency'], $out_currency, false);
                    $products[$k]['quantity'] = (int) $p['quantity'];
                    $products[$k]['currency'] = $out_currency;
                    if (isset($p['purchase_price'])) {
                        $products[$k]['purchase_price'] = (float) shop_currency($p['purchase_price'], $p['currency'], $out_currency, false);
                    }
                }
            } else {
                if (!$in_currency) {
                    $in_currency = $products['currency'];
                }
                $products['price'] = (float) shop_currency($products['price'], $in_currency, $out_currency, false);
                if (isset($products['purchase_price'])) {
                    $products['purchase_price'] = (float) shop_currency($products['purchase_price'], $products['currency'], $out_currency, false);
                }
                $products['currency'] = $out_currency;
            }
        }
        return $products;
    }

    /**
     * Add purchase price to products
     * 
     * @param array $products
     * @return array
     */
    public static function addPurchasePrice($products)
    {
        $find_price = array();
        if ($products) {
            foreach ($products as $k => $p) {
                if (!isset($p['purchase_price'])) {
                    $find_price[$k] = isset($p['sku_id']) ? $p['sku_id'] : $k;
                }
            }
            if ($find_price) {
                $sku_model = new shopProductSkusModel();
                $pm = new shopProductModel();
                $sql = "SELECT ps.id, ps.purchase_price, p.currency FROM {$sku_model->getTableName()} ps "
                        . "LEFT JOIN {$pm->getTableName()} p ON ps.product_id = p.id "
                        . "WHERE ps.id IN ('" . implode("','", $find_price) . "')";
                $skus = $sku_model->query($sql)->fetchAll('id');
                foreach ($find_price as $ck => $sku_id) {
                    if (isset($skus[$sku_id])) {
                        $products[$ck]['purchase_price'] = shop_currency($skus[$sku_id]['purchase_price'], $skus[$sku_id]['currency'], null, false);
                    }
                }
            }
        }
        return $products;
    }

    public static function getImagineQuantity()
    {
        return self::$imagine_quantity;
    }

    public static function setImagineQuantity($quantity)
    {
        self::$imagine_quantity = (int) $quantity;
    }

    public static function secureString($str, $mode = ENT_QUOTES, $charset = 'UTF-8')
    {
        return htmlentities($str, $mode, $charset);
    }

    /**
     * Check coupon life
     * 
     * @param string $coupon
     * @return false|array
     */
    public static function couponCheck($coupon)
    {
        if ($coupon) {
            $scm = new shopFlexdiscountCouponPluginModel();
            $coupon = $scm->getByField("code", $coupon);
            $today = time();
            // Если срок действия купона истек
            if ($coupon['expire_datetime'] && ($today > strtotime($coupon['expire_datetime']))) {
                return false;
            }
            // Если достигнут предел по количеству использований купона
            if ($coupon['limit'] > 0 && $coupon['used'] >= $coupon['limit']) {
                return false;
            }
            return $coupon;
        }
        return false;
    }

    /**
     * Get code of block
     * 
     * @param string $id - block ID
     * @param array $params - assign vars
     * @return string - HTML
     */
    public static function getBlock($id, $params = array())
    {
        wa('site');
        $site_block_model = new siteBlockModel();
        $block = $site_block_model->getById($id);
        if ($block) {
            if ($params) {
                wa()->getView()->assign($params);
            }
            return wa()->getView()->fetch('string:' . $block['content']);
        } else {
            return '';
        }
    }

    /**
     * Get plugin settings
     * 
     * @return type
     */
    public static function getSettings()
    {
        static $settings = array();
        if (!$settings) {
            $sm = new shopFlexdiscountSettingsPluginModel();
            $settings = $sm->getSettings();
        }
        return $settings;
    }

    /**
     * Check whether user field-mask is equal to mask or not
     * 
     * @param string $mask - user mask
     * @param string $value - user value
     * @return boolean|string
     */
    public static function isValidMask($mask, $value)
    {
        // Если выбраны "Скидка на всё", либо "Отмена скидок" тогда прерываем проверку
        if ($mask == "=" || $mask == "-") {
            return $mask;
        }
        // Создаем из переданного значения пользователя маску
        $user_mask = preg_replace(array("/\d+/", "/\s/"), array("D", ""), $value);
        $mask = preg_replace(array("/\d+/", "/\w+/"), "D", $mask);

        $value = preg_replace("/\s/", "", $value);

        // Если маски не равны
        if ($user_mask !== $mask) {
            // Проверяем есть ли у маски хеш на конце
            $hash = substr($mask, -1);
            if ($hash == '#') {
                // Если у значения пользователя нету хеша на конце, то добавляем его
                if (substr($user_mask, -1) !== '#') {
                    $user_mask .= "#";
                    if ($user_mask !== $mask) {
                        return false;
                    } else {
                        $value .= "#";
                    }
                }
            } else {
                return false;
            }
        }
        // Делаем проверку на ноль
        preg_match("/\d+/", $value, $matches);
        if (empty($matches[0])) {
            return false;
        }
        return $value;
    }

}
