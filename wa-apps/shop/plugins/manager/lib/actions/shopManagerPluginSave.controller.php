<?php

class shopManagerPluginSaveController extends waJsonController
{
    public function execute()
    {
        $order_id = waRequest::post('order_id');
        $manager_id = waRequest::post("manager_id");
        $manager = new waContact($manager_id);
        $order_model = new shopOrderModel();
        $order = $order_model->getById($order_id);
        if ($order &&
            (wa()->getUser()->isAdmin('shop') ||
                (!$order['paid_date'] && wa()->getUser()->getId() == $order['manager_id']))) {
            $order_model->updateById($order_id, array('manager_id' => $manager_id));
            $order_log_model = new shopOrderLogModel();
            $order_log_model->add(array(
                'order_id' => $order_id,
                'action_id' => '',
                'text' => 'Указан менеджер <b>'.htmlspecialchars($manager->getName()).'</b>',
                'before_state_id' => $order['state_id'],
                'after_state_id' => $order['state_id'],
            ));

        } else {
            $this->errors = 'Вы не можете редактировать менеджера';
        }
    }
}