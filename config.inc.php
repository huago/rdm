<?php
$config = array(
		//路由
		'_urls' => array(
				'/^$/' => array(
						'controller' => 'IndexController',
						'action'	 => 'indexAction'
				),
		),
		'_iniFile' 			=> ROOT_PATH . 'internal/site.config.ini',
		'_modelsHome'      	=> ROOT_PATH . 'models',
		'_controllersHome' 	=> ROOT_PATH . 'controllers',
		'_viewsHome'       	=> ROOT_PATH . 'views',
);

/**
 * 加载ini配置
 */
$config = array_merge($config, Cola_Helper_Tool::parseIniFile($config['_iniFile']));
//初始化MC配置
ini_set('memcache.hash_function','crc32');
ini_set('memcache.hash_strategy','consistent');
//共享session
@ini_set("session.save_handler", "memcache");
ini_set("session.save_path", "tcp://127.0.0.1:11211");