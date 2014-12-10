<?php 
App::uses('HttpSocket', 'Network/Http');
App::uses('NLog', 'Lib');
//HttpSocket封装
class Http{
	//$query:q=cakephp 或 array('q' => 'cakephp')
	public static function get($url, $query = array()){
		$start = explode(' ', microtime());
		$HttpSocket = new HttpSocket();
		$results = $HttpSocket->get($url, $query);
		$resultsStr = json_encode($results);
		NLog::w('HttpSocket-get url:'.$url.' query:'.json_encode($query).' time:'.NLog::lt($start).' result('.strlen($resultsStr).'):'.substr($resultsStr, 0, 200));
		return $results;
	}
	//get，以json形式返回数据
	public static function getJson($url, $query = array()){
		$results = self::get($url, $query);
		if(isset($results->body)){
			return json_decode($results->body);
		}
		return false;
	}
	
	public static function post($url, $data = array()){
		$HttpSocket = new HttpSocket();
		// string data
		$results = $HttpSocket->post($url, $data);
	//	print_r($results);
		echo json_encode($results).'<hr>';
		return $results;
	}

	
	public static function curlGet($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$result = curl_exec($ch);
		$error = curl_error($ch);
		NLog::w('-curlPost--error:'.json_encode($error));
	//	$result = trim(urldecode($result));
		curl_close($ch);
		return $result;
	}
	
	public static function curlPost($url, $data){
		$d = http_build_query($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$result = curl_exec($ch);
		$error = curl_error($ch);
		NLog::w('-curlPost--error:'.json_encode($error));
	//	$result = trim(urldecode($result));
		curl_close($ch);
		return $result;
	}
	
	public static function xmlPost($url, $inputXml){
	//	$log = 'Unionpay - xmlHttp - url('.$url.') - input('.$inputXml.') ';
		$upompXml = urlencode($inputXml);
		$header[] = "Content-type: text/plain";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, trim($upompXml));
		
		$result = curl_exec($ch);
		$error = curl_error($ch);
		$result = trim(urldecode($result));
		curl_close($ch);
		NLog::w("http-xmlPost--".$url."--".$inputXml.'--resutl:'.$result);
		return $result;
	}
	
	public static function xmlHttpsPost($url, $inputXml, $verifypeer = true){
		NLog::fw('http', $url);
		NLog::fw('http', $inputXml);
	//	$log = 'Unionpay - xmlHttp - url('.$url.') - input('.$inputXml.') ';
		$upompXml = urlencode($inputXml);
	//	$upompXml = $inputXml;
		$header[] = "Content-type: text/plain";
		$ch = curl_init();
	/*	curl_setopt($ch, CURLOPT_URL, trim($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, trim($upompXml));
	*/	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifypeer); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); 
		curl_setopt($ch, CURLOPT_SSLVERSION, 2); 
		curl_setopt($ch, CURLOPT_HEADER, $header); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $upompXml);
		
		$result = curl_exec($ch);
		$error = curl_error($ch);
		$result = trim(urldecode($result));
		curl_close($ch);
		NLog::fw('http', "xmlHttpsPost--".$url."--".$upompXml.'--resutl:'.json_encode($result));
		return $result;
	}
	
	public static function httpsPost($url, $input){
	//	$input = urlencode($input);
		$header[] = "Content-type: text/plain";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
		
		$result = curl_exec($ch);
		$error = curl_error($ch);
	//	print_r($error);
		
		$result = trim(urldecode($result));
		curl_close($ch);
		NLog::fw('http', "httpsPost--".$url."--".$input.'--resutl:'.json_encode($result));
//		NLog::w("http-HttpsPost--".$url."--".$input.'--resutl:'.$result.'---error:'.json_encode($error));
		return $result;
	}
	
	public static function httpsGet($url){
		$ch = curl_init(); 
		$header[] = "Content-type: text/plain";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);
		curl_close($ch);
		$result = trim(urldecode($response));
		NLog::w("----http-httpsGet---resutl:".$result);
		return $result;
	}
	//原样专发http请求
	function asRequest($url, $deviceId = 102){
		NLog::w('as-request--start--'.$url);
		$gets = $GLOBALS['_GET'];
		unset($gets['url']);
		$deviceId ? $gets['device_id'] = $deviceId : '';
		$now = explode(' ', microtime());
		$method = '';
		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			$method = 'get';
			$url .= '?'.http_build_query($gets);
			NLog::w('as-request--get('.$url.'):');
			$r = self::curlGet($url);
		}else{
			$url .= '?'.http_build_query($gets);
			if(defined('GLOBALS_HTTPRAWPOSTDATA')){
				$method = 'xmlPost';
				NLog::w('as-request--xmlPost('.$url.'):'.GLOBALS_HTTPRAWPOSTDATA);
				$r = self::xmlPost($url, GLOBALS_HTTPRAWPOSTDATA);
			}else{
				$method = 'post';
				if(isset($GLOBALS['_POST']['context_data'])) $GLOBALS['_POST']['context_data'] = stripslashes($GLOBALS['_POST']['context_data']);
				if(isset($GLOBALS['_POST']['notify_data'])) $GLOBALS['_POST']['notify_data'] = stripslashes($GLOBALS['_POST']['notify_data']);
				NLog::w('as-request--post('.$url.'):'.json_encode($GLOBALS['_POST']));
				$r = self::curlPost($url, $GLOBALS['_POST']);
			}
		}
		$log = $method.'--'.('time:'.NLog::lt($now)).'--url:'.$url.'--post:'.json_encode($GLOBALS['_POST']).'--HTTPRAWPOSTDATA:'
				.(defined('GLOBALS_HTTPRAWPOSTDATA') ? GLOBALS_HTTPRAWPOSTDATA : 'NO_HTTPRAWPOSTDATA').'--return:'.$r;
		NLog::fw('as-request', $log);
		NLog::w('as-request--return:'.$r);
		return $r;
	}
}












