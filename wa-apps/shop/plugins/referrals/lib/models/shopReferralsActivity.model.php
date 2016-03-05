<?php

class shopReferralsActivityModel extends waModel
{
    protected $table = 'shop_referrals_activity';

    public function add($code, $referer)
    {
        $referrals_model = new shopReferralsModel();
        $referral = $referrals_model->getById($code);
        if ($referer) {
            $referer_domain = parse_url($referer, PHP_URL_HOST);
        } else {
            $referer_domain = '';
        }
        if ($referral) {
            $this->insert(array(
                'create_datetime' => date('Y-m-d H:i:s'),
                'code' => $code,
                'contact_id' => $referral['contact_id'],
                'referer' => $referer,
                'referer_host' => $referer_domain,
            ));
        }
    }

    public function getStat($contact_id, $group_by, $start_date)
    {
        if ($group_by == 'date') {
            $group_by = 'DATE(create_datetime)';
        } elseif (!$this->fieldExists($group_by)) {
            return array();
        }
        $sql = "SELECT ".$group_by." f, count(*) FROM ".$this->table."
                WHERE contact_id = i:0 AND create_datetime >= s:1
                GROUP BY ".$group_by;
        return $this->query($sql, $contact_id, $start_date)->fetchAll('f', true);
    }

    public function getAllStat($group_by, $start_date)
    {
        if ($group_by == 'date') {
            $group_by = 'DATE(create_datetime)';
        } elseif (!$this->fieldExists($group_by)) {
            return array();
        }
        $sql = "SELECT ".$group_by." f, count(*) FROM ".$this->table."
                WHERE create_datetime >= s:0
                GROUP BY ".$group_by;
        return $this->query($sql, $start_date)->fetchAll('f', true);
    }
}
