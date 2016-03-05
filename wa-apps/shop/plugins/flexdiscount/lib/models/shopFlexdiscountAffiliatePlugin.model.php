<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountAffiliatePluginModel extends waModel
{

    protected $table = 'shop_flexdiscount_affiliate';

    /**
     * Get contact affiliate bonus by order_id
     * 
     * @param int $order_id
     * @return array|false
     */
    public function getByOrder($order_id)
    {
        return $this->getByField("order_id", (int) $order_id);
    }

    /**
     * Set status of order equal to done
     * 
     * @param int $order_id
     * @return bool
     */
    public function done($order_id)
    {
        return $this->updateByField("order_id", (int) $order_id, array("status" => 1));
    }

    /**
     * Set status of order equal to process
     * 
     * @param type $order_id
     * @return type
     */
    public function cancel($order_id)
    {
        return $this->updateByField("order_id", (int) $order_id, array("status" => 0));
    }

    /**
     * Check if order is enable to add affiliate bonus
     * 
     * @param type $order_id
     * @return type
     */
    public function isEnabled($order_id)
    {
        $status = $this->select("status")->where("order_id = '" . (int) $order_id . "'")->fetchField();
        return $status ? 0 : 1;
    }

}
