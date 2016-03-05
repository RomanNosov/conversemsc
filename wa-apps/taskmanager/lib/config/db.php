<?php
return array(
    'taskmanager' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'created' => array('datetime', 'null' => 0),
        'deadline' => array('datetime'),
        'author_id' => array('int', 11, 'null' => 0),
        'performer_id' => array('int', 11, 'null' => 0),
        'author_name' => array('varchar', 255, 'null' => 0),
        'performer_name' => array('varchar', 255, 'null' => 0),
        'status' => array('varchar', 255, 'null' => 0),
        'title' => array('text', 'null' => 0),
        'text' => array('text', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);
