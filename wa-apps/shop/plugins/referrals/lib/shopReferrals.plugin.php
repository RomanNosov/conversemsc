<?php

class shopReferralsPlugin extends shopPlugin
{
    public function frontendHead()
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        if ($promo_id = waRequest::get('promo_id')) {
            if (waRequest::cookie('promo_id') != $promo_id) {
                $n = $this->getSettings('cookie_expire');
                if (!$n) {
                    $n = 30;
                }
                wa()->getResponse()->setCookie('promo_id', $promo_id, time() + $n * 86400);
                if ($referer = waRequest::server('HTTP_REFERER')) {
                    wa()->getResponse()->setCookie('promo_referer', $referer, time() + $n * 86400, '', $this->getCookieDomain());
                }
                if ($this->getSettings('activity')) {
                    $activity_model = new shopReferralsActivityModel();
                    $activity_model->add($promo_id, $referer);
                }
            }
            $url = wa()->getConfig()->getRequestUrl(false, true);
            $get_params = waRequest::get();
            unset($get_params['promo_id']);
            $url_params = http_build_query($get_params);
            wa()->getResponse()->redirect($url.($url_params ? '?'.$url_params : ''), 301);
        }
    }

    private function getCookieDomain()
    {
        if ($this->getSettings('cookie_main_domain')) {
            $domain = waRequest::server('HTTP_HOST');
            if (substr($domain, 0, 4) == 'www.') {
                $domain = substr($domain, 4);
            }
            $domain_parts = explode('.', $domain);
            if (count($domain_parts) <= 2) {
                return '.'.$domain;
            }
            if ((strlen($domain_parts[count($domain_parts) - 2]) <= 3) && count($domain_parts) == 3) {
                return '.'.$domain;
            }
            array_shift($domain_parts);
            return '.'.implode('.', $domain_parts);
        }
        return '';
    }

    public function backendSettingsAffiliate()
    {
        return array(
            'id' => 'referrals',
            'name' => _wp('Referrals'),
            'url' => '?plugin=referrals&module=affiliate&action=settings'
        );
    }

    public function backendOrder($order)
    {
        if (!empty($order['params']['referrals_code'])) {
            $referrals_model = new shopReferralsModel();
            $referral = $referrals_model->getById($order['params']['referrals_code']);
            $c = new waContact($referral['contact_id']);
            return array(
                'aux_info' => _wp('Referred by').': <strong><a href="?action=customers#/id/'.$referral['contact_id'].'/">'.htmlspecialchars($c->getName()).'</a>'
            );
        }
    }

    public function frontendMyAffiliate()
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        if (waRequest::get('terms')) {
            echo $this->getSettings('terms');
            exit;
        }

        $contact_id = wa()->getUser()->getId();
        $referrals_model = new shopReferralsModel();
        $referral = $referrals_model->getByContactId($contact_id);

        if (!$referral && waRequest::post('terms')) {
            $referral = array(
                'contact_id' => $contact_id
            );
            $referral['code'] = $referrals_model->add($contact_id);
        }
        $view = wa()->getView();
        if ($referral) {

            /**
             * @var shopViewHelper $helper
             */
            $helper = wa('shop')->getView()->getHelper()->shop;
            $categories = $helper->categories(0, 0);
            if ($categories) {
                $c_id = array_rand($categories);
                $c = $categories[$c_id];
                $category_url = wa()->getConfig()->getHostUrl().$c['url'];
            } else {
                $category_url = wa()->getRouteUrl('shop/frontend/category', array('category_url' => 'some-category-url'), true);
            }
            $view->assign('category_url', $category_url.'?promo_id='.$referral['code']);

            $pc = new shopProductsCollection();
            $products = $pc->getProducts('*', 0, 10);

            if ($products) {
                $p_id = array_rand($products);
                $product_url = wa()->getConfig()->getHostUrl().$products[$p_id]['frontend_url'];
            } else {
                $product_url = wa()->getRouteUrl('shop/frontend/product', array(
                    'category_url' => 'some-category-url', 'product_url' => 'some-product-url'), true);
            }
            $view->assign('product_url', $product_url.'?promo_id='.$referral['code']);

            $view->assign('code', $referral['code']);
            $view->assign('data', $this->getReportsData($referral));

            $format = waDateTime::getFormat('date');
            $format = preg_replace('#[,/\.-]?(Y|y)[,/\.-]?#i', '', $format);
            $format = preg_replace('/([a-z])/i', '%$1', $format);

            $view->assign('date_format', $format);
            $view->assign('activity', $this->getSettings('activity'));
        }
        $view->assign('promo', $this->getSettings('promo'));
        return $view->fetch($this->path . '/templates/frontendMyAffiliate.html');
    }

    /**
     * @param $customer
     * @return array
     */
    public function backendCustomer($customer)
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        $referrals_model = new shopReferralsModel();
        $referral = $referrals_model->getByContactId($customer['contact_id']);
        if ($referral) {
            $view = wa()->getView();
            $view->assign('code', $referral['code']);
            $view->assign('data', $this->getReportsData($referral));

            $format = waDateTime::getFormat('date');
            $format = preg_replace('#[,/\.-]?(Y|y)[,/\.-]?#i', '', $format);
            $format = preg_replace('/([a-z])/i', '%$1', $format);

            $view->assign('date_format', $format);
            $view->assign('activity', $this->getSettings('activity'));

            return array(
                'info_section' => '<h2>'._wp('Referrals').'</h2>'.$view->fetch($this->path.'/templates/report.html')
            );
        }
    }

    /**
     * @return array
     */
    public function backendCustomers()
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        $referral_model = new shopReferralsModel();
        return array(
            'sidebar_top_li' => '<li id="s-referrals"><span class="count">'.$referral_model->countAll().'</span>
                <a href="#/referrals/"><i class="icon16" style="background-image: url(\''.$this->getPluginStaticUrl().'img/referrals.png\'); background-size: 16px 16px;"></i>'._wp('Referrals').'</a>
                </li><script src="'.$this->getPluginStaticUrl().'js/customers.js?v'.$this->getVersion().'"></script>'
        );
    }

    /**
     * @param array $referral
     * @return array
     */
    protected function getReportsData($referral)
    {
        $start_date = date('Y-m-d', strtotime('-30 day'));

        $is_activity = $this->getSettings('activity');
        if ($is_activity) {
            $activity_model = new shopReferralsActivityModel();
            $visitors_by_date = $activity_model->getStat($referral['contact_id'], 'date', $start_date);
        }

        $tm = new shopAffiliateTransactionModel();
        $sql = "SELECT DATE(create_datetime) d, SUM(amount) amount, SUM(IF(amount > 0, 1, 0)) count
                FROM ".$tm->getTableName()."
                WHERE contact_id = i:0 AND create_datetime >= s:1 AND type LIKE 'referral_%'
                GROUP BY DATE(create_datetime)";
        $transactions = $tm->query($sql, $referral['contact_id'], $start_date)->fetchAll('d');

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
                WHERE t.contact_id = i:0 AND t.create_datetime >= s:1 AND t.type LIKE 'referral_%'
                GROUP BY p.value";
            $referer_stat = $tm->query($sql, $referral['contact_id'], $start_date)->fetchAll('r');
            if ($is_activity) {
                $referer_visitors = $activity_model->getStat($referral['contact_id'], 'referer_host', $start_date);
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
                                        WHERE contact_id = i:0 AND create_datetime >= s:1 AND
                                        type LIKE 'referral_%'", $referral['contact_id'], $start_date)->fetch();
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

    /**
     * @param array $params
     */
    public function orderCreate($params)
    {
        if (!$this->getSettings('enabled') || wa()->getEnv() == 'backend') {
            return;
        }
        $referral_code = waRequest::cookie('promo_id');
        if ($referral_code) {
            $referral_model = new shopReferralsModel();
            if ($referral = $referral_model->getById($referral_code)) {
                $order_id = $params['order_id'];
                $order_model = new shopOrderModel();
                $order = $order_model->getById($order_id);
                if ($order['contact_id'] != $referral['contact_id']) {
                    $params_model = new shopOrderParamsModel();
                    $order_params = array(
                        'referrals_code' => $referral_code,
                    );
                    if ($ref = waRequest::cookie('promo_referer')) {
                        $order_params['referrals_referer'] = $ref;
                        $order_params['referrals_referer_host'] = parse_url($ref, PHP_URL_HOST);
                    }
                    $params_model->set($order_id, $order_params, false);
                }
            }
        }
    }

    /**
     * @param array $params
     */
    public function applyBonus($params)
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        $order_id = $params['order_id'];

        $order_model = new shopOrderModel();
        $order = $order_model->getById($order_id);

        $order_params_model = new shopOrderParamsModel();
        $order_params = $order_params_model->get($order_id);

        if ($order['paid_date'] && !empty($params['update']['paid_date']) && !empty($order_params['referrals_code'])) {
            $referrals_model = new shopReferralsModel();
            $referral = $referrals_model->getById($order_params['referrals_code']);
            if ($referral) {
                $customer_model = new shopCustomerModel();
                $customer_model->createFromContact($referral['contact_id']);
                $amount = shopAffiliate::calculateBonus($order_id, $this->getSettings('rate'));
                $tm = new shopAffiliateTransactionModel();
                $tm->applyBonus($referral['contact_id'], $amount, $order_id, sprintf(_wp('Bonus for referred customer’s order %s %s'),
                    shopHelper::encodeOrderId($order_id), ifset($order_params['referrals_referer'], '')), 'referral_bonus');
                if ($this->getSettings('notifications')) {
                    try {
                        $this->sendNotification($referral, array(
                            'order_id' => shopHelper::encodeOrderId($order_id),
                            'amount' => $amount
                        ));
                    } catch (Exception $e) {
                        echo $e;
                    }
                }
            }
        }
    }

    /**
     * @param array $params
     */
    public function cancelBonus($params)
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        $order_id = $params['order_id'];

        $order_params_model = new shopOrderParamsModel();
        $order_params = $order_params_model->get($order_id);

        if (!empty($order_params['referrals_code'])) {
            $referrals_model = new shopReferralsModel();
            $referral = $referrals_model->getById($order_params['referrals_code']);

            $tm = new shopAffiliateTransactionModel();
            $row = $tm->getLast($referral['contact_id'], $order_id);
            if ($row && $row['amount'] > 0) {
                $tm->applyBonus($referral['contact_id'], -$row['amount'], $order_id, sprintf(_wp('Cancel bonus for referred customer’s order %s'),
                    shopHelper::encodeOrderId($order_id)), 'referral_cancel');
            }
        }
    }

    /**
     * @param array $referral
     * @param array $params
     * @return int
     */
    protected function sendNotification($referral, $params)
    {
        $view = wa()->getView();
        $view->assign($params);

        $referral = new shopCustomer($referral['contact_id']);
        $email = $referral->get('email', 'default');
        if (!$email) {
            return;
        }
        $view->assign('referral', $referral);
        $view->assign('balance', $referral->affiliateBonus());

        $subject = sprintf(_wp('+%s to your referral bonus'), waLocale::format(round($params['amount'], 2), false));
        $body = $view->fetch($this->path.'/templates/notification.html');

        $general = wa('shop')->getConfig()->getGeneralSettings();

        $message = new waMailMessage($subject, $body);
        $message->setTo($email);
        if ($general['email']) {
            $message->setFrom($general['email'], $general['name']);
        }
        return $message->send();
    }


    /** Handler for `reset` event: truncate all shop tables and delete all settings */
    public function reset()
    {
        $m = new waModel();
        $m->query("TRUNCATE `shop_referrals_activity`");
        $m->query("TRUNCATE `shop_referrals`");
    }
}
