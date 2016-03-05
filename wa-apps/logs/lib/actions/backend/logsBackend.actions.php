<?php

class logsBackendActions extends waViewActions
{
    public function preExecute()
    {
        if (waRequest::get('action') != 'file') {
            wa()->getResponse()->setCookie('back_url', wa()->getConfig()->getCurrentUrl());
        }
        $this->setLayout(new logsBackendLayout());
        $this->view->assign(array(
            'rights' => $this->getRights(),
            'admin'  => wa()->getUser()->isAdmin('logs'),
        ));
    }

    public function defaultAction()
    {
        if (strpos(waRequest::server('HTTP_REFERER'), logsHelper::getLogsBackendUrl()) !== 0) {
            //on first app access, show files sorted by update time
            $this->redirect('?action=files&mode=updatetime');
        } else {
            //otherwise default view mode is root log directory
            $this->execute('directory');
        }
    }

    public function directoryAction()
    {
        $this->view->assign('items', logsHelper::getDirectory(waRequest::get('path')));
    }

    public function fileAction()
    {
        $path = waRequest::get('path');
        $page = waRequest::get('page', null, waRequest::TYPE_INT);
        $file = logsHelper::getFile(array(
            'path' => $path,
            'page' => $page,
        ));
        if ($page !== null && ($page < 1 || $page > $file['page_count'])) {
            $this->redirect('?action=file&path='.$path);
        } else {
            $logs_backend_url = logsHelper::getLogsBackendUrl(false);
            $this->view->assign(array(
                'file'     => $file,
                'back'     => strpos(waRequest::cookie('back_url'), $logs_backend_url) === 0,
                'back_url' => waRequest::cookie('back_url', $logs_backend_url),
            ));
        }
    }

    public function filesAction()
    {
        $mode = waRequest::get('mode');
        $method = 'getFilesBy'.ucfirst($mode);
        if (method_exists('logsHelper', $method)) {
            $this->view->assign('items', logsHelper::$method());
        } else {
            $this->redirect(wa()->getAppUrl());
        }
    }
}
