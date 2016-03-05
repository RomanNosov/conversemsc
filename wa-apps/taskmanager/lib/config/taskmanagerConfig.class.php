<?php

class taskmanagerConfig extends waAppConfig {

	function onCount() {
        $model = new taskmanagerModel();
		$tasks = $model->query(
				"SELECT COUNT(*) as count FROM taskmanager WHERE performer_id = i:id AND status = 'new'", array('id' => wa()->getUser()->getId()))
				->fetchAll();

		return $tasks[0]["count"];
    }
}