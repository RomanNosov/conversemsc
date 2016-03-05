<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountCouponPluginModel extends waModel
{

    protected $table = 'shop_flexdiscount_coupon';

    /**
     * Get coupon info
     * 
     * @param int $id
     * @return array
     */
    public function getCoupon($id)
    {
        return $this->getById($id);
    }

    /**
     * Check if coupon already exist
     * 
     * @param string $code - coupon code
     * @return bool - true - coupon doesn't exist; false - coupon exist 
     */
    public function checkCoupon($code)
    {
        $coupon_exist = (bool) $this->getByField("code", $code);
        return $coupon_exist ? false : true;
    }

    /**
     * Generate coupon code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $alphabet = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890";
        $result = '';
        while (strlen($result) < 8) {
            $result .= $alphabet{mt_rand(0, strlen($alphabet) - 1)};
        }
        return $result;
    }

    /**
     * Save / update coupon
     * 
     * @param int $coupon_id
     * @param array $coupon
     * @return bool|int
     */
    public function save($coupon_id, $coupon)
    {
        if ($coupon_id) {
            if ($this->updateById($coupon_id, $coupon)) {
                return $coupon_id;
            }
        } else {
            return $this->insert($coupon);
        }
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

    /**
     * Delete coupon and discount relations
     * 
     * @param int $coupon_id
     * @return bool
     */
    public function delete($coupon_id)
    {
        if ($coupon_id) {
            // Удаляем купон
            $this->deleteById($coupon_id);
            // Удаление заказов у купонов
            $sfcom = new shopFlexdiscountCouponOrderPluginModel();
            $sfcom->deleteByField("coupon_id", $coupon_id);

            // Обнуляем купон у скидок
            $sm = new shopFlexdiscountPluginModel();
            $discounts = $sm->getDiscounts();
            if ($discounts) {
                foreach ($discounts as $d) {
                    if ($d['coupon_id'] == $coupon_id) {
                        // Обновление может вызвать ошибку в связи с возможностью дублирования полей при 
                        // изменении значения купона. Если возникнет ошибка, то необходимо удалить повторяющееся
                        // правило скидок
                        try {
                            $sm->updateById($d['id'], array("coupon_id" => 0));
                        } catch (Exception $e) {
                            $sm->deleteById($d['id']);
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Increment coupon used
     * 
     * @param int $coupon_id - coupon ID
     */
    public function useOne($coupon_id)
    {
        $sql = "UPDATE {$this->table} SET used = used + 1 WHERE id = :id";
        $this->exec($sql, array('id' => $coupon_id));
    }

    /**
     * Get all coupons
     * 
     * @param bool $full
     * @return array
     */
    public function getCoupons($full = false)
    {
        $coupons = array();
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->query($sql);
        $time = time();
        if ($result) {
            if ($full) {
                $sfcom = new shopFlexdiscountCouponOrderPluginModel();
                $orders = $sfcom->getCouponOrders();
            }
            foreach ($result as $row) {
                $coupons[$row['id']] = $row;
                $coupons[$row['id']]['block'] = 0;
                if ($row['expire_datetime'] && strtotime($row['expire_datetime']) < $time) {
                    $coupons[$row['id']]['block'] = 1;
                }
                // Если достигнут предел по количеству использований купона
                if ($row['limit'] > 0 && $row['used'] >= $row['limit']) {
                    $coupons[$row['id']]['block'] = 1;
                }
                if ($full && isset($orders[$row['id']])) {
                    $coupons[$row['id']]['orders'] = $orders[$row['id']];
                }
            }
        }
        return $coupons;
    }

    /**
     * Get coupon by order ID
     * 
     * @param int $order_id
     * @return int|false
     */
    public function getCouponByOrderId($order_id)
    {
        $com = new shopFlexdiscountCouponOrderPluginModel();
        $sql = "SELECT c.* FROM {$this->table} c 
                LEFT JOIN {$com->getTableName()} co ON co.coupon_id = c.id
                WHERE co.order_id = '" . (int) $order_id . "'";
        return $this->query($sql)->fetchAssoc();
    }

}
