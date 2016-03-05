<?php

class shopReferralsPluginCustomersAction extends shopCustomersListAction
{
    public function execute()
    {
        $start = waRequest::request('start', 0, 'int');
        $limit = 50;
        $order = waRequest::request('order', '!last_order');

        $config = $this->getConfig();
        $use_gravatar     = $config->getGeneralSettings('use_gravatar');
        $gravatar_default = $config->getGeneralSettings('gravatar_default');

        // Get customers
        $scm = new shopReferralsCustomerModel();
        list ($customers, $total) = $scm->getReferralsList($start, $limit, $order);
        $has_more = $start + count($customers) < $total;
        $countries = array();
        foreach ($customers as &$c) {
            $c['affiliate_bonus'] = (float) $c['affiliate_bonus'];
            if (!$c['photo'] && $use_gravatar) {
                $c['photo'] = shopHelper::getGravatar(!empty($c['email']) ? $c['email'] : '', 50, $gravatar_default);
            } else {
                $c['photo'] = waContact::getPhotoUrl($c['id'], $c['photo'], 50, 50);
            }
            $c['categories'] = array();
            if (!empty($c['address']['region']) && !empty($c['address']['country'])) {
                $countries[$c['address']['country']] = array();
            }
            $c['name'] = waContactNameField::formatName($c);
        }
        unset($c);

        // Add region names to addresses
        if ($countries) {
            $rm = new waRegionModel();
            foreach($rm->where('country_iso3 IN (?)', array_keys($countries))->query() as $row) {
                $countries[$row['country_iso3']][$row['code']] = $row['name'];
            }
            foreach ($customers as &$c) {
                if (!empty($c['address']['region']) && !empty($c['address']['country'])) {
                    $country = $c['address']['country'];
                    $region = $c['address']['region'];
                    if (!empty($countries[$country]) && !empty($countries[$country][$region])) {
                        $c['address']['region_formatted'] = $countries[$country][$region];
                    }
                }
            }
            unset($c);
        }

        // Contact categories
        $ccm = new waContactCategoryModel();
        $categories = $ccm->getAll('id');
        if ($customers) {
            $ccsm = new waContactCategoriesModel();
            foreach($ccsm->getContactsCategories(array_keys($customers)) as $c_id => $list) {
                foreach($list as $cat_id) {
                    if (!empty($categories[$cat_id])) {
                        $customers[$c_id]['categories'][$cat_id] = $categories[$cat_id];
                    }
                }
            }
        }

        $count = count($customers);

        // Set up lazy loading
        if (!$has_more) {
            // Do not trigger lazy loading, show total count at end of list
            $total_customers_number = $start + count($customers);
        } else {
            $total_customers_number = null; // trigger lazy loading
        }


        $title = _wp('Referrals');
        $hash_start = '#/referrals/0/';
        $discount = null;


        $this->view->assign('cols', self::getCols());
        $this->view->assign('title', $title);
        $this->view->assign('order', $order);
        $this->view->assign('total_count', $total);
        $this->view->assign('discount', $discount);
        $this->view->assign('customers', $customers);
        $this->view->assign('hash_start', $hash_start);
        $this->view->assign('category_id', '');
        $this->view->assign('total_customers_number', $total_customers_number);
        $this->view->assign('refferals_reports_data', $this->getReportsData());
        $this->view->assign('count', $count);
        $this->view->assign('offset', $start);
        $this->view->assign('in_lazy_process', waRequest::get('lazy', false));    // is now lazy loading?


        /**
         * @var shopReferralsPlugin $plugin
         */
        $plugin = wa()->getPlugin('referrals');
        $this->view->assign('activity', $plugin->getSettings('activity'));
        $this->view->assign('customers_template', $this->getConfig()->getAppPath('templates/actions/customers/CustomersList.html'));
        $format = waDateTime::getFormat('date');
        $format = preg_replace('#[,/\.-]?(Y|y)[,/\.-]?#i', '', $format);
        $format = preg_replace('/([a-z])/i', '%$1', $format);

        $this->view->assign('date_format', $format);

    }

    /**
     * @param array $referral
     * @return array
     */
    protected function getReportsData()
    {
        /**
         * @var shopReferralsPlugin $plugin
         */
        $plugin = wa()->getPlugin('referrals');
        $start_date = date('Y-m-d', strtotime('-30 day'));

        $is_activity = $plugin->getSettings('activity');
        if ($is_activity) {
            $activity_model = new shopReferralsActivityModel();
            $visitors_by_date = $activity_model->getAllStat('date', $start_date);
        }

        $tm = new shopAffiliateTransactionModel();
        $sql = "SELECT DATE(create_datetime) d, SUM(amount) amount, SUM(IF(amount > 0, 1, 0)) count
                FROM ".$tm->getTableName()."
                WHERE create_datetime >= s:0 AND type LIKE 'referral_%'
                GROUP BY DATE(create_datetime)";
        $transactions = $tm->query($sql, $start_date)->fetchAll('d');

        $result = array(
            'visitors' => array(),
            'amount' => array(),
        );

        $ts = strtotime($start_date);
        while ($ts < time()) {
            $d = date('Y-m-d', $ts);
            if ($is_activity) {
                $result['visitors'][] = array(
                    $d, ifset($visitors_by_date[$d], 0)
                );
            }
            $result['amount'][] = array(
                $d, (float)ifset($transactions[$d]['amount'], 0)
            );
            $ts += 86400;
        }
        if ($is_activity) {
            $result['visitors_max'] = $visitors_by_date ? max($visitors_by_date) : 0;
        }
        try {
            $sql = "SELECT IFNULL(p.value, '') r, COUNT(DISTINCT t.order_id) transactions, SUM(amount) amount FROM " . $tm->getTableName() . " t
                LEFT JOIN shop_order_params p ON t.order_id = p.order_id AND p.name = 'referrals_referer_host'
                WHERE t.create_datetime >= s:0 AND t.type LIKE 'referral_%'
                GROUP BY p.value";
            $referer_stat = $tm->query($sql, $start_date)->fetchAll('r');
            if ($is_activity) {
                $referer_visitors = $activity_model->getAllStat('referer_host', $start_date);
            }

            $max = 0;
            foreach ($referer_stat as $k => &$r) {
                if ($is_activity) {
                    $r['visitors'] = ifset($referer_visitors[$k], 0);
                }
                if ($r['amount'] < 0) {
                    $r['amount'] = 0;
                }
                $r['amount'] = round($r['amount'], 2);
                if ($r['amount'] > $max) {
                    $max = $r['amount'];
                }
            }
            unset($r);

            if (!$max) {
                $max = 1;
            }
            if ($is_activity) {
                foreach ($referer_visitors as $k => $n) {
                    if (!isset($referer_stat[$k])) {
                        $referer_stat[$k] = array(
                            'visitors' => $n,
                            'amount' => 0,
                            'transactions' => 0,
                        );
                    }
                }
                $max_w = $referer_visitors ? max($referer_visitors) : 0;
                if (!$max_w) {
                    $max_w = 1;
                }
            }

            foreach ($referer_stat as &$r) {
                if ($is_activity) {
                    $r['visitors_w'] = 150 + round(170 * $r['visitors'] / $max_w);
                    $r['conversion'] = $r['transactions'] * 100 / ifempty($r['visitors'], 1);
                    if ($r['conversion'] > 100) {
                        $r['conversion'] = 100;
                    }
                }
                $r['w'] = round(150 * $r['amount'] / $max);
            }
            unset($r);
            $total = $tm->query("SELECT SUM(amount) amount, COUNT(DISTINCT order_id) transactions FROM ".$tm->getTableName()."
                                        WHERE create_datetime >= s:0 AND
                                        type LIKE 'referral_%'", $start_date)->fetch();
            $result['total'] = array(
                'amount' => $total['amount'],
                'transactions' => $total['transactions'],
            );
            if ($is_activity) {
                $result['total']['visitors'] = array_sum($referer_visitors);
                $result['total']['conversion'] = $result['total']['transactions'] * 100 / ifempty($result['total']['visitors'], 1);
                if ($result['total']['conversion'] > 100) {
                    $result['total']['conversion'] = 100;
                }
            }
            $result['referer'] = $referer_stat;
        } catch (waDbException $e) {
            waLog::log($e);
            return array();
        }

        return $result;
    }
}