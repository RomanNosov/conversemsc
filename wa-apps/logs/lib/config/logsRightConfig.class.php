<?php

class logsRightConfig extends waRightConfig
{
    public function init()
    {
        $this->addItem('delete_files', _w('Can delete files'), 'checkbox');
        $this->addItem('change_settings', _w('Can change settings'), 'checkbox');
        $this->addItem('view_phpinfo', _w('Can view PHP configuration'), 'checkbox');
    }
}
