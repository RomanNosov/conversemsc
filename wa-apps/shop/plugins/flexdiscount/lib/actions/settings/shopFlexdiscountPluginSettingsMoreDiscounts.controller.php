<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginSettingsMoreDiscountsController extends waJsonController
{

    public function execute()
    {
        if (waRequest::method() == 'post') {
            $page = waRequest::post('page', 1, waRequest::TYPE_INT);
            if ($page) {
                $this->response['end'] = 0;
                $fd = new shopFlexdiscountPluginModel();

                // Получаем настройки
                $settings = shopFlexdiscountHelper::getSettings();

                // Количество товаров на странице
                $perpage = (!empty($settings['perpage']) ? $settings['perpage'] : 30);

                $page = max(1, $page);
                // Общее количество скидок
                $count = $fd->countAll();
                // Всего страниц
                $max_page = ceil($count / $perpage);
                // Если текущая страница больше максимальной, то присваиваем текущей максимальной значение 
                if ($page > $max_page) {
                    $page = $max_page;
                    $this->response['end'] = 1;
                }
                // Действующие скидки
                $discounts = $fd->getDiscounts(true, array("limit" => $perpage, "offset" => ($page - 1) * $perpage));
                $this->response['discounts'] = $discounts;
            }
        }
    }

}
