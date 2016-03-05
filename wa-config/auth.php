<?php
return array (
  'www.rus.convershop.ru' => 
  array (
    'auth' => true,
    'app' => 'shop',
  ),
  'tetatet-nk.ru' => 
  array (
    'auth' => true,
    'app' => 'shop',
    'params' => 
    array (
      'confirm_email' => '1',
      'button_caption' => 'Регистрация',
    ),
    'fields' => 
    array (
      'firstname' => 
      array (
        'caption' => 'Имя',
        'placeholder' => '',
      ),
      'lastname' => 
      array (
        'caption' => 'Фамилия',
        'placeholder' => '',
      ),
      'email' => 
      array (
        'caption' => 'Email',
        'placeholder' => '',
        'required' => true,
      ),
      'password' => 
      array (
        'caption' => 'Пароль',
        'placeholder' => '',
        'required' => true,
      ),
      'middlename' => 
      array (
        'caption' => 'Отчество',
        'placeholder' => '',
      ),
      'company' => 
      array (
        'caption' => 'Компания',
        'placeholder' => '',
      ),
      'phone' => 
      array (
        'caption' => 'Телефон',
        'placeholder' => '',
      ),
      'im' => 
      array (
        'caption' => 'Мгновенные сообщения',
        'placeholder' => '',
      ),
    ),
  ),
  'conversemsc.ru' => 
  array (
    'auth' => true,
    'app' => 'shop',
    'params' => 
    array (
      'confirm_email' => '1',
      'button_caption' => 'Регистрация',
    ),
    'fields' => 
    array (
      'firstname' => 
      array (
        'required' => 'true',
        'caption' => 'Имя',
        'placeholder' => '',
      ),
      'lastname' => 
      array (
        'required' => 'true',
        'caption' => 'Фамилия',
        'placeholder' => '',
      ),
      'email' => 
      array (
        'caption' => 'Email',
        'placeholder' => '',
        'required' => true,
      ),
      'password' => 
      array (
        'caption' => 'Пароль',
        'placeholder' => '',
        'required' => true,
      ),
      'sex' => 
      array (
        'required' => 'true',
        'caption' => 'Пол',
        'placeholder' => '',
      ),
      'phone' => 
      array (
        'required' => 'true',
        'caption' => 'Телефон',
        'placeholder' => '',
      ),
      'timezone' => 
      array (
        'required' => 'true',
        'caption' => 'Часовой пояс',
        'placeholder' => '',
      ),
    ),
    'rememberme' => true,
  ),
);
