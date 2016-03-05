<?php

class shopManagerOrderModel extends shopOrderModel
{
    public function getReportsByManager($start_date = null, $end_date = null)
    {
        $paid_date_sql = self::getDateSql('o.paid_date', $start_date, $end_date);
        $sql = "SELECT o.manager_id,
                    SUM(o.total*o.rate) AS sales,
                    SUM(o.shipping*o.rate) AS shipping,
                    SUM(o.tax*o.rate) AS tax,
                    COUNT(*) AS `count`
                FROM {$this->table} AS o
                WHERE {$paid_date_sql}
                GROUP BY o.manager_id";
        $result = array();
        foreach ($this->query($sql) as $row) {
            foreach (array('sales', 'shipping', 'tax') as $k) {
                $row[$k] = (float)$row[$k];
            }
            $result[$row['manager_id']] = $row;
        }

        // purchase
        $sql = "SELECT o.manager_id,
                    SUM(IF(oi.purchase_price > 0, oi.purchase_price*o.rate, ps.purchase_price*pcur.rate)*oi.quantity) AS purchase
                FROM ".$this->table." AS o
                    JOIN shop_order_items AS oi
                        ON oi.order_id=o.id
                    JOIN shop_product AS p
                        ON oi.product_id=p.id
                    JOIN shop_product_skus AS ps
                        ON oi.sku_id=ps.id
                    JOIN shop_currency AS pcur
                        ON pcur.code=p.currency
                WHERE oi.type='product' AND $paid_date_sql
                GROUP BY o.manager_id";
        foreach ($this->query($sql) as $row) {
            $result[$row['manager_id']]['purchase'] = (float)$row['purchase'];
            $row = &$result[$row['manager_id']];
            $row['profit'] = $row['sales'] - $row['purchase'] - $row['shipping'] - $row['tax'];
            unset($row);
        }
        return $result;
    }


    public function countPayments($start_date = null, $end_date = null)
    {
        $sql = "SELECT p.value f, count(*) count, SUM(o.total * o.rate) total  FROM ".$this->table." o
                LEFT JOIN shop_order_params p ON o.id = p.order_id AND p.name = 'payment_id'
                WHERE ".self::getDateSql('o.paid_date', $start_date, $end_date)."
                GROUP BY p.value";
        return $this->query($sql)->fetchAll('f', true);
    }


    public function countDeletedByManager($start_date = null, $end_date = null)
    {
        $sql = "SELECT manager_id, count(*) FROM ".$this->table."
                WHERE ".self::getDateSql('create_datetime', $start_date, $end_date)." AND state_id = 'deleted'
                GROUP BY manager_id";
        return $this->query($sql)->fetchAll('manager_id', true);
    }

    public function countDeleted($start_date, $end_date)
    {
        $sql = "SELECT count(*) FROM ".$this->table."
                WHERE ".self::getDateSql('create_datetime', $start_date, $end_date)." AND state_id = 'deleted'";
        return (int)$this->query($sql)->fetchField();
    }
}