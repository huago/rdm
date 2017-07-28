<?php
/**
 * 主目录启动文件，配置调试信息，加载类库，加载代码库版本信息等准备数据
 * @author cup_chenyubo
 * @version 2013-10-15
 */
header("Content-type: text/html; charset=utf-8");
ini_set('display_errors', 'on');
error_reporting(E_ALL);
@session_start();
session_name('_z4h2s6n1M0q7j0x2H9y5c4y2');
date_default_timezone_set('Asia/Shanghai');
define('ROOT_PATH', preg_replace("/\/+/is", '/', dirname(__FILE__)) . '/');
define('LIBRARY_PATH', preg_replace("/\/+/is", '/', dirname(__FILE__)) . '/library/');
define('WEB_HOST' , 'http://openrdm.com');//主站地址
require_once LIBRARY_PATH . 'Cola/Cola.php';
$cola = Cola::getInstance();
require_once ROOT_PATH . 'controllers/BaseController.php';
try{
	$cola->boot(ROOT_PATH . 'config.inc.php')->dispatch();
}catch(Exception $e){
	echo $e->getMessage();die;
}
