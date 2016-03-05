<?php

class shopReferralsCustomerModel extends shopCustomerModel
{
    public function getReferralsList($start=0, $limit=50, $order='name')
    {
        $start = (int) $start;
        $limit = (int) $limit;

        $join = array();
        $select = array(
            'sc.*, c.*, o.create_datetime AS last_order_datetime'
        );

        $join[] = 'JOIN shop_referrals AS r ON r.contact_id=c.id';

        if ($join) {
            $join = implode("\n", $join);
        } else {
            $join = '';
        }

        $possible_orders = array(
            'name' => 'c.name',
            '!name' => 'c.name DESC',
            'total_spent' => 'sc.total_spent',
            '!total_spent' => 'sc.total_spent DESC',
            'affiliate_bonus' => 'sc.affiliate_bonus',
            '!affiliate_bonus' => 'sc.affiliate_bonus DESC',
            'number_of_orders' => 'sc.number_of_orders',
            '!number_of_orders' => 'sc.number_of_orders DESC',
            'last_order' => 'sc.last_order_id',
            '!last_order' => 'sc.last_order_id DESC',
            'registered' => 'c.create_datetime',
            '!registered' => 'c.create_datetime DESC',
        );

        if (!$order || empty($possible_orders[$order])) {
            $order = key($possible_orders);
        }
        $order = 'ORDER BY '.$possible_orders[$order];

        // Fetch basic contact and customer info
        $sql = "SELECT SQL_CALC_FOUND_ROWS ".implode(', ', $select)."
                FROM wa_contact AS c
                    LEFT JOIN shop_customer AS sc
                        ON c.id=sc.contact_id
                    LEFT JOIN shop_order AS o
                        ON o.id=sc.last_order_id
                    $join
                GROUP BY c.id
                $order
                LIMIT {$start}, {$limit}";

        $customers = $this->query($sql)->fetchAll('id');

        $total = $this->query('SELECT FOUND_ROWS()')->fetchField();

        // get emails
        $ids = array_keys($customers);
        if ($ids) {
            foreach ($this->query("
                SELECT contact_id, email, MIN(sort)
                FROM `wa_contact_emails`
                WHERE contact_id IN (".implode(',', $ids).")
                GROUP BY contact_id") as $item)
            {
                $customers[$item['contact_id']]['email'] = $item['email'];
            }
        }

        if (!$customers) {
            return array(array(), 0);
        }

        // Fetch addresses
        foreach($customers as &$c) {
            $c['number_of_orders'] = (int)$c['number_of_orders'];
            $c['address'] = array();
        }
        unset($c);

        $sql = "SELECT *
                FROM wa_contact_data
                WHERE contact_id IN (i:ids)
                    AND sort=0
                    AND field LIKE 'address:%'
                ORDER BY contact_id";
        foreach ($this->query($sql, array('ids' => array_keys($customers))) as $row) {
            $customers[$row['contact_id']]['address'][substr($row['field'], 8)] = $row['value'];
        }

        return array($customers, $total);
    }
}
