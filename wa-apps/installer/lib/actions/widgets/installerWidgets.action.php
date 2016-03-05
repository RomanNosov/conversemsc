<?php

/*
 * This file is part of Webasyst framework.
 *
 * Licensed under the terms of the GNU Lesser General Public License (LGPL).
 * http://www.webasyst.com/framework/license/
 *
 * @link http://www.webasyst.com/
 * @author Webasyst LLC
 * @copyright 2011 Webasyst LLC
 * @package installer
 */

class installerWidgetsAction extends installerItemsAction
{
    protected $module = 'widgets';


    protected function getAppOptions()
    {
        $options = parent::getAppOptions();
        $options += array(
            'widgets' => true,
        );
        return $options;
    }

    protected function extendApplications(&$applications)
    {
        parent::extendApplications($applications);
        $applications['webasyst'] = array(
            'name' => _w('Webasyst Framework'),
            'id'   => 'webasyst',
            'slug' => 'webasyst',
        );
    }
}
