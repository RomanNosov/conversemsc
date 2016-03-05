<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountSettingsPluginModel extends waModel
{

    protected $table = 'shop_flexdiscount_settings';

    /**
     * Get setting
     * 
     * @param string $name
     * @return array
     */
    public function get($name)
    {
        return $this->query("SELECT value FROM " . $this->table . " WHERE field = s:field", array("field" => $name))->fetchField();
    }

    /**
     * Get all settings
     * 
     * @return array
     */
    public function getSettings()
    {
        $settings = array();
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->query($sql);
        if ($result) {
            foreach ($result as $r) {
                if ($r['ext']) {
                    $settings[$r['field']][$r['ext']] = $r['value'];
                } else {
                    $settings[$r['field']] = $r['value'];
                }
            }
        }
        return $settings;
    }

    /**
     * Save settings
     * 
     * @param array $settings
     * @return boolean
     */
    public function save($settings)
    {
        $query = array();
        // Очищаем таблицу
        $this->exec("DELETE FROM " . $this->table);
        if ($settings && is_array($settings)) {
            foreach ($settings as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $ext => $v2) {
                        $query[] = "('" . $this->escape($k) . "', '" . $this->escape($ext) . "', '" . $this->escape($v2) . "')";
                    }
                } else {
                    $query[] = "('" . $this->escape($k) . "', '', '" . $this->escape($v) . "')";
                }
            }
        }
        if ($query) {
            $sql = "INSERT IGNORE INTO {$this->table} (field, ext, value) VALUES " . implode(",", $query);
            return $this->exec($sql);
        }
        return true;
    }

}
