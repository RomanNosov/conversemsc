<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginModel extends waModel
{

    protected $table = 'shop_flexdiscount';

    /**
     * Get all discounts with coupons
     * 
     * @param bool $with_coupons - if true, than add info abount coupons
     * @param array $filter
     * @return array
     */
    public function getDiscounts($with_coupons = false, $filter = array())
    {
//        $limit = 30;
//        $offset = 0;
//        if (!empty($filter['limit'])) {
//            $limit = (int) $filter['limit'];
//        }
//        if (!empty($filter['offset'])) {
//            $offset = (int) $filter['offset'];
//        }
        $discounts = array();
        $domain_id = "";
        
        // Фильтр по Витринам
        if (!empty($filter['domain'])) {
            wa("site");
            $domain_model = new siteDomainModel();
            $domains = $domain_model->getAll('name');
            if (isset($domains[$filter['domain']])) {
                $domain_id = " AND (domain_id = 0 OR domain_id = '".(int) $domains[$filter['domain']]['id']."')";
            }
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE 1 $domain_id ORDER BY sort ASC";
        $result = $this->query($sql);
        $time = time();
        if ($result) {
            // Получаем информацию о купонах
            if ($with_coupons) {
                $coupon_model = new shopFlexdiscountCouponPluginModel();
                $coupons = $coupon_model->getCoupons();
            }
            foreach ($result as $k => $d) {
                $discounts[$k] = $d;
                $discounts[$k]['block'] = 0;
                if ($with_coupons) {
                    if (isset($coupons[$d['coupon_id']])) {
                        $discounts[$k]['coupon'] = $coupons[$d['coupon_id']];
                        if ($coupons[$d['coupon_id']]['block']) {
                            $discounts[$k]['block'] = 1;
                        }
                    }
                }
                if (isset($d['expire_datetime']) && strtotime($d['expire_datetime']) < $time) {
                    $discounts[$k]['block'] = 1;
                }
            }
        }
        return $discounts;
    }

    /**
     * Delete all discounts
     * 
     * @return bool
     */
    public function clear()
    {
        $sql = "DELETE FROM {$this->table}";
        return $this->exec($sql);
    }

    /**
     * Multiple insert ignore
     * 
     * @param array $data
     * @return boolean
     */
    public function multipleIgnoreInsert($data)
    {
        if (!$data) {
            return true;
        }
        $values = array();
        $fields = array();
        if (isset($data[0])) {
            foreach ($data as $row) {
                $row_values = array();
                foreach ($row as $field => $value) {
                    if (isset($this->fields[$field])) {
                        $row_values[$this->escapeField($field)] = $this->getFieldValue($field, $value);
                    }
                }
                if (!$fields) {
                    $fields = array_keys($row_values);
                }
                $values[] = implode(',', $row_values);
            }
        } else {
            $multi_field = false;
            $row_values = array();
            foreach ($data as $field => $value) {
                if (isset($this->fields[$field])) {
                    if (is_array($value) && !$multi_field) {
                        $multi_field = $field;
                        $row_values[$this->escapeField($field)] = '';
                    } else {
                        $row_values[$this->escapeField($field)] = $this->getFieldValue($field, $value);
                    }
                }
            }
            $fields = array_keys($row_values);
            if ($multi_field) {
                foreach ($data[$multi_field] as $v) {
                    $row_values[$this->escapeField($multi_field)] = $this->getFieldValue($multi_field, $v);
                    $values[] = implode(',', $row_values);
                }
            } else {
                $values[] = implode(',', $row_values);
            }
        }
        if ($values) {
            $sql = "INSERT IGNORE INTO " . $this->table . " (" . implode(',', $fields) . ") VALUES (" . implode('), (', $values) . ")";
            return $this->query($sql);
        }
    }

}
