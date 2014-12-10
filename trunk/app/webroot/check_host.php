<?php 
//$servername 	= 'production';
$servername 	= 'local';
$host			= $_SERVER['HTTP_HOST'];
switch ($host){
	case "wx1.jkl.com":
		$servername 	= 'local';
		break;
	case "xxx":
		$servername 	= 'dev';
		break;
	case "182.92.184.19":
	case "182.92.184.19:8001":
		$servername 	= 'production';
		break;
}

define('SERVERNAME', 	$servername);

if(!empty($_SERVER['HTTP_X_REAL_IP'])){
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
}

if(isset($GLOBALS["HTTP_RAW_POST_DATA"]) && $GLOBALS["HTTP_RAW_POST_DATA"]){
	define('GLOBALS_HTTPRAWPOSTDATA', $GLOBALS["HTTP_RAW_POST_DATA"]);
}