<?php

class shopManagerPlugin extends shopPlugin
{
    protected static $users;

    public function backendOrders()
    {
        $this->addJs('js/manager.js?v'.$this->getVersion());

        $contact_id = wa()->getUser()->getId();
        $order_model = new shopOrderModel();
        $count_all = $order_model->countByField('manager_id', $contact_id);
        $count_pending = $order_model->countByField(array('manager_id' => $contact_id, 'state_id' => array('new', 'processing', 'paid')));

        return array(
            'sidebar_top_li' => '
            <li id="s-manager-pending-orders" class="list">
                <span class="count">'.$count_pending.'</span>
                <a href="#/orders/hash/manager-pending/">
                    <i class="icon16 ss orders-processing"></i>Мои в обработке
                </a>
            </li>
            <li id="s-manager-my-orders" class="list">
                <span class="count">'.$count_all.'</span>
                <a href="#/orders/hash/manager/">
                    <i class="icon16 ss orders-all"></i>Все мои заказы
                </a>
            </li>'
        );
    }

    public function backendOrder($order)
    {
        if ($order['manager_id']) {
            $manager = new waContact($order['manager_id']);
            $manager = $manager->getName();
        } else {
            $manager = 'Не назначен';
            if ($order['state_id'] == 'new') {
                return;
            }
        }
        $can_edit = wa()->getUser()->isAdmin('shop') ||
            (!$order['paid_date'] && wa()->getUser()->getId() == $order['manager_id']);

        $result = array(
            'title_suffix' =>
                '<span style="float:right; margin-right: 20px" class="small"><em class="hint">Менеджер: <span data-contact_id="'.$order['manager_id'].'" id="manager">'.htmlspecialchars($manager).'</span>'.
($can_edit ? ' <a href="#" id="edit-manager"><i style="vertical-align: middle; margin-top: -2px" class="icon10 edit"></i></a>' : '')
.'</em></span>'
        );
        return $result;
    }

    public function backendReports()
    {
        $this->addJs('js/reports.js?v'.$this->getVersion());
        return array(
            'menu_li' => '<li><a href="#/managers/">Менеджеры</a></li>'
        );
    }

    public function orderAction($data)
    {
        $order_id = $data['order_id'];
        $order_model = new shopOrderModel();
        $order = $order_model->getById($order_id);

        if (!$order['manager_id'] && wa()->getEnv() == 'backend') {
            $order_model->updateById($order_id, array(
                'manager_id' => wa()->getUser()->getId()
            ));
        }
    }

    public function ordersCollection($params)
    {
        /**
         * @var shopOrdersCollection $collection
         */
        $collection = $params['collection'];
        $hash = $collection->getType();
        if ($hash !== 'manager' && $hash !== 'manager-pending') {
            return null;
        }
        $collection->addWhere('o.manager_id = '.wa()->getUser()->getId());
        if ($hash == 'manager-pending') {
            $collection->addWhere("o.state_id IN ('new','processing','paid')");
        }
        return true;
    }

    public static function getUsers()
    {
        if (self::$users === null) {
            self::$users = array();
            $rights_model = new waContactRightsModel();
            $contact_ids = $rights_model->getUsers('shop');
            if ($contact_ids) {
                $contact_model = new waContactModel();
                self::$users = $contact_model->getName($contact_ids);
            }
        }
        return self::$users;
    }
}