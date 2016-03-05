<?php

class taskmanagerBackendApiController extends waJsonController {

	function execute() {

		if (waRequest::post('type') == "users") {
            $this->response = array(
            	"users" => $this->getUsers(),
            	"currentUser" => array(
            		"id" => wa()->getUser()->getId(),
            		"name" => wa()->getUser()->getName()
            	)
            );
        }

		if (waRequest::post('type') == "appoint") {
            $this->response = $this->appoint(waRequest::post('title'), waRequest::post('to'), waRequest::post('deadline'), waRequest::post('text'));
        }

		if (waRequest::post('type') == "tasks") {
            $this->response = $this->tasks(waRequest::post('status'), waRequest::post('page'));
        }

		if (waRequest::post('type') == "myTasksCount") {
            $this->response = $this->myTasksCount();
        }

		if (waRequest::post('type') == "setTaskStatus") {
            $this->response = $this->setTaskStatus(waRequest::post('id'), waRequest::post('status'));
        }

	}

	function getUsers() {
		$col = new waContactsCollection('/search/is_user=1&id!=' . wa()->getUser()->getId()); // wa()->getUser()->getId()
        $contacts = $col->getContacts('id,name', 0, 5000);
        // var_dump($col);
        return $contacts;
	}

	function getUser($id) {
		$col = new waContactsCollection('/search/id=' . $id);
        $contacts = $col->getContacts('name', 0, 5000);
        
        if (!array_key_exists($id, $contacts)) {
        	return "";
        }
        
        return $contacts[$id]["name"];
	}

	function appoint($title, $to, $deadline, $text) {

		$model = new taskmanagerModel();
		$task['id'] = $model->insert($task = array(
			"created" => date("Y-m-d H:i:s"),
			"author_id" => wa()->getUser()->getId(),
			"author_name" => wa()->getUser()->getName(),
			"performer_id" => $to,
			"performer_name" => $this->getUser($to),
			"status" => "new",
			"deadline" => date("Y-m-d H:i:s", strtotime($deadline)),
			"title" => preg_replace(
                "/(\#100(\d+))/",
                '<a href="/webasyst/shop/#/orders/id=$2/" target="_blank">$1</a>',
                $title
            ),
			"text" => preg_replace(
                "/(\#100(\d+))/",
                '<a href="/webasyst/shop/#/orders/id=$2/" target="_blank">$1</a>',
				preg_replace(
	                "/((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-]*)*\/?)/",
	                '<a href="$1" target="_blank">$1</a>',
	                nl2br(htmlspecialchars($text))
	            ))
		));

		if ($task["deadline"] == "0000-00-00 00:00:00") {
			$task["deadline"] = null;
		}

		$task["deadline"] = date("d.m.Y", strtotime($task["deadline"]));

		if ($task["deadline"] == "01.01.1970") {
			$task["deadline"] = null;
		}

		return $task;
	}

	function tasks($status, $page = 0) {

		$model = new taskmanagerModel();
		$offset = $page * 60;

		if ($status == "my") {
			$tasks = $model->query(
				"SELECT * FROM taskmanager WHERE author_id = i:id ORDER BY created DESC LIMIT i:offset, 60", array(
					'id' => wa()->getUser()->getId(), 
					'offset' => $offset
				))
				->fetchAll();
		} else {
			$tasks = $model->query(
				"SELECT * FROM taskmanager WHERE performer_id = i:id AND status = s:status ORDER BY created DESC LIMIT i:offset, 60", array(
					'id' => wa()->getUser()->getId(), 
					'status' => $status, 
					'offset' => $offset
				))
				->fetchAll();
		}

		foreach ($tasks as &$task) {

			if ($task["deadline"] == null) {
				continue;
			}

			if ($task["deadline"] == "0000-00-00 00:00:00") {
				$task["deadline"] = null;
			}

			$task["deadline"] = date("d.m.Y", strtotime($task["deadline"]));

			if ($task["deadline"] == "01.01.1970") {
				$task["deadline"] = null;
			}
		}

		return $tasks;
	}

	function myTasksCount() {
		$model = new taskmanagerModel();
		$tasks = $model->query(
				"SELECT COUNT(*) as count FROM taskmanager WHERE performer_id = i:id AND status = 'new'", array('id' => wa()->getUser()->getId()))
				->fetchAll();

		return $tasks[0]["count"];
	}

	function setTaskStatus($id, $status) {
		$model = new taskmanagerModel();
		$model->exec("UPDATE taskmanager SET status = s:status WHERE id = i:id", array(
			'id' => $id, 
			'status' => $status
		));
		return array("message" => "ok");
	}
}