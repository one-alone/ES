<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', '/');
define('APP_PATH', dirname(__FILE__) . DS);

Date_default_timezone_set("PRC");
$GLOBALS = require(APP_PATH . '../config.php');

if(!empty($GLOBALS['startSession'])){
    session_start();
}

require_once(APP_PATH . "helper.php");
require_once(APP_PATH . "controller.php");
require_once(APP_PATH . "model.php");
require_once(APP_PATH . "orm.php");
//new Orm();
require_once(APP_PATH . "view.php");

set_error_handler( ["Helper", "customError"]);
register_shutdown_function(function (){
    $fatalMsg = error_get_last();
    if($fatalMsg){
        Helper::log($fatalMsg,  Helper::FATAL_ERROR);
    }
});

//设置路由规范
Helper::setRoute();

//定义全局变量
$__module = $_REQUEST['m'];
$__controller = $_REQUEST['c'];
$__action = $_REQUEST['a'];
$__param = $_REQUEST['_p'];
spl_autoload_register(function ($class) use ($__module) {
    foreach (array_merge($GLOBALS['plugins'], ['model', 'controller' . DS . $__module, './']) as $dir) {
        $file = APP_PATH . '../' . $dir . DS . $class . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
});

//开始运行
Helper::start();