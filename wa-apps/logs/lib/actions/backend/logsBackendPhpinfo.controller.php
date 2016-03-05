<?php

class logsBackendPhpinfoController extends waController
{
    public function execute()
    {
        if ($this->getRights('view_phpinfo')) {
            phpinfo();
        } else {
            $this->redirect(wa()->getAppUrl());
        }
    }
}
