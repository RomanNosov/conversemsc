<?php

class installerWidgetsInfoAction extends waViewAction
{
    public function execute()
    {
        $filter = array();
        $filter['enabled'] = true;

        $options = array(
            'installed' => true,
        );

        $search = array();

        $search['slug'] = preg_replace('@/.+$@', '', waRequest::get('slug'));
        if ($search['slug'] == 'webasyst') {
            $options['widgets'] = true;
        }
        $applications = installerHelper::getInstaller()->getApps($options, $filter);
        $widget_search = array();
        $widget_search['id'] = preg_replace('@^.+/@', '', waRequest::get('slug'));
        if (array_filter($search, 'strlen') && ($app = installerHelper::search($applications, $search))) {
            $widget_search['slug'] = $search['slug']."/widgets/".$widget_search['id'];
            $options = array(
                'action' => true,
                //        'requirements' => true,
                //XXX   'vendor'       => waRequest::get('widget_vendor', 'webasyst'),
                // 'installed'=>true,
            );
            $widget = null;
            $messages = array();
            try {
                $widget = installerHelper::getInstaller()->getItemInfo($widget_search['slug'], $options);
            } catch (Exception $ex) {
                installerHelper::handleException($ex, $messages);
            }
            if (!$widget) {
                $options['local'] = true;
                $widget = installerHelper::getInstaller()->getItemInfo($widget_search['slug'], $options);

            }
            $this->view->assign('identity_hash', installerHelper::getHash());
            $this->view->assign('promo_id', installerHelper::getPromoId());
            $this->view->assign('domain', installerHelper::getDomain());
            $this->view->assign('widget', $widget);
            $this->view->assign('app', $applications[$widget['app']]);
            $this->view->assign('messages', $messages);
            $this->view->assign('query', waRequest::get('query', '', waRequest::TYPE_STRING_TRIM).'/');
        } else {
            throw new waException(_w('Widget not found'), 404);
        }
    }
}
