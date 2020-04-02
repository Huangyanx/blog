<?php
header("Content-type:text/html;charset=utf-8");
include "init.php";//初始化文件
include_once ROOT_PATH.'/core/config.php';//配置文件
include_once ROOT_PATH.'/core/Model.class.php';//模板文件
include_once ROOT_PATH.'/core/View.class.php';//
include_once ROOT_PATH.'/core/Controller.class.php';//
$controller=new controller();
$controller->run();
?>