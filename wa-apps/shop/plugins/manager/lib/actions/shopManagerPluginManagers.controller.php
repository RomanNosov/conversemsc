<?php

class shopManagerPluginManagersController extends waJsonController
{
    public function execute()
    {
        $ids = waRequest::post('ids', array(), waRequest::TYPE_ARRAY_INT);
        if (!$ids) {
            return;
        }

        // get managers
        $order_model = new shopOrderModel();
        $sql = "SELECT o.id, o.manager_id FROM shop_order o WHERE o.id IN (i:ids) AND o.manager_id > 0";
        $rows = $order_model->query($sql, array('ids'  => $ids))->fetchAll('id', true);

        if ($rows) {
            $contact_model = new waContactModel();
            $managers = $contact_model->getName($rows);
        }
        foreach ($rows as $order_id => $manager_id) {
            if (isset($managers[$manager_id])) {
                $this->response[$order_id] = array('id' => $manager_id, 'name' => htmlspecialchars($managers[$manager_id]));
            }
        }
        foreach ($ids as $id) {
            if (!isset($this->response[$id])) {
                $this->response[$id] = null;
            }
        }
    }
}