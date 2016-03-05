<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopInstantorderPluginModel extends waModel {

    protected $table = 'shop_instantorder';

    public function truncate() {
        $sql = "TRUNCATE TABLE {$this->table}";
        return $this->query($sql);
    }

}
