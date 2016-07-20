<?php
return array(
	'modules' => array(
        'Zend\Router',
        'Zend\Validator',
		'NetglueTwilio',
	),
	'module_listener_options' => array(
		'config_glob_paths'    => array(
			__DIR__ . '/config/{,*.}{global,local}.php',
		),
		'module_paths' => array(
			__DIR__.'/../vendor',
		),
	),
);

