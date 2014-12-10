<?php 
//$servername 	= 'production';
$servername 	= 'local';
switch ($host){
	case "wx1.jkl.com":
		$servername 	= 'local';
		break;
	case "xxx":
		$servername 	= 'dev';
		break;
	case "182.92.184.19":
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