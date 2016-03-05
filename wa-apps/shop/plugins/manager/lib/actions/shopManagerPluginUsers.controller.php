<?php

class shopManagerPluginUsersController extends waController
{
    public function execute()
    {
        $contact_id = waRequest::request('contact_id');
        $users = shopManagerPlugin::getUsers();
        $html = '<select>';
        if (!$contact_id) {
            $html .= "<option value='' selected></option>";
        }
        foreach ($users as $id => $name) {
            $html .= '<option'.($contact_id == $id ? ' selected' : '').' value="'.$id.'">'.
                        htmlspecialchars($name).'</option>';
        }
        $html .= '</select>';
        echo $html;
    }
}