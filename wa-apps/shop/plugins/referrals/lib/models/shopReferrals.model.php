<?php

class shopReferralsModel extends waModel
{
    protected $table = 'shop_referrals';
    protected $id = 'code';

    /**
     * @param $contact_id
     * @return mixed
     */
    public function getByContactId($contact_id)
    {
        return $this->getByField('contact_id', $contact_id);
    }

    /**
     * @param $contact_id
     * @return int
     */
    public function add($contact_id)
    {
        do {
            $code = rand(100, 1000000);
        } while ($this->getById($code));

        $this->insert(array(
            'code' => $code,
            'contact_id' => $contact_id,
            'create_datetime' => date('Y-m-d H:i:s')
        ));
        return $code;
    }
}