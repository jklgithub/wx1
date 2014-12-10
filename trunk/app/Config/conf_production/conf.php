<?php 
include_once '/home/conf/wx1.php';
$config['conf']['host'] 						= $_SERVER['HTTP_HOST'];

$config['conf']['log']['path'] 					= '/home/logs/wx1/app/';
$config['yees']['log']['logName'] 				= 'app';		//名称
$config['yees']['log']['level'] 				= 6;				//记录级别
$config['yees']['log']['service'] 				= 'dev';		//当前的服务点

