<?php
/**
 * 六级日志
 */
define('NLOG_FATAL', 1);
define('NLOG_ERROR', 2);
define('NLOG_WARNING', 3);
define('NLOG_NOTICE', 4);
define('NLOG_DEBUG', 5);
define('NLOG_INFO', 6);
/**
 * 日志包装类
 * 时间 | 服务点 | 日志级别 | username | sessionId | ip | 
 */
class NLog extends CakeLog{
	/**
	 * 指定日志文件名称的日志
	 */
	function fw($logName, $output, $level = NLOG_DEBUG){
		self::w($output, $level, false, false, false, $logName);
	}
	/**
	 * 允许参数格式为数组
	 */
	function w_($pars){
		$level = isset($pars['level']) ? $pars['level'] : NLOG_DEBUG;
		$output = isset($pars['output']) ? $pars['output'] : '';
		$callSource =  isset($pars['source']) ? $pars['source'] : false;
		$input =  isset($pars['input']) ? $pars['input'] : false;
		$username = isset($pars['username']) ? $pars['username'] : false;
		self::w($output, $level, $callSource, $input, $username);
	}
	/**
	 * level	日志级别		
	 * 			NLOG_INFO		6 info/ 
	 * 			NLOG_DEBUG		5 debug/ 
	 * 			NLOG_NOTICE		4 notice/ 
	 * 			NLOG_WARNING	3 warning/ 
	 * 			NLOG_ERROR		2 error/ 
	 * 			NLOG_FATAL		1 fatal
	 * $callSource 调用源
	 * 时间、slog | 服务点 | 日志级别 | username | sessionid | ip | controller.action.xx | input | output
	 */
	function w($output, $level = NLOG_DEBUG, $callSource = false, $input = false, $username = false, $logName = false){
	//	print_r($this);
		//服务点--配置
		$service = Configure::read('conf.log.service');//.(isset($_SERVER) ? ' - '.gethostbyname($_SERVER["SERVER_NAME"]).'' : '');
		//当前服务器的日志级别--配置
		$serviceLevel = Configure::read('conf.log.level');
		//分隔符
		$separator = ' | ';
		//级别显示
		$levelArr = array(
			NLOG_INFO 		=> 'INFO',
			NLOG_DEBUG		=> 'DEBUG',
			NLOG_NOTICE 	=> 'NOTICE',
			NLOG_WARNING 	=> 'WARNING',
			NLOG_ERROR 		=> 'ERROR',
			NLOG_FATAL 		=> 'FATAL',
		);
		//级别
		if(isset($levelArr[$level])){
			if($level > $serviceLevel){
				return;
			}
			$level_ = $levelArr[$level];
		}else{
			//TODO : error level
			echo '<hr>no level<hr>';
			return;
		}
		//username
		if($username === false){
			$username = 'no-user';
			$sessionid = '';
			if(isset($this) && isset($this->Session) && $this->Session->check('login_user_id')){
				$username = $this->Session->read('login_user_id');
			}else if(isset($_GET['uid'])){
				$username = $_GET['uid'];
			}
			//微信相关，记录微信ID
			if(isset($this) && isset($this->Session) && $this->Session->check('wexin_id')){
				$username = $this->Session->read('wexin_id');
			}
		}
		//sessionid
		$sesionId = session_id();
		//ip
		$ip = $_SERVER['REMOTE_ADDR'];
		//调用源
		if($callSource === false){
			$callSource = isset($this) ? get_class($this) : '';
			if(isset($this) && isset($this->action)){
				$callSource .= '->'.$this->action;
			}
		}
		//输入
		if($input === false){
			if(isset($this) && isset($this->params)){
				if(isset($this->params['url']) && count($this->params['url']) > 1){
					$gets = array();
					foreach($this->params['url'] as $k => $v){
						if($k != 'url'){
							$gets[$k] = $v;
						}
					}
					$input .= 'GET'.json_encode($gets);
				}
				if(isset($this->params['form']) && count($this->params['form']) > 0){
					$input .= 'POST'.json_encode($this->params['form']);
				}
			}
		}
		if(!is_string($input)){
			$input = json_encode($input);
		}
		if(strlen($input) > 80){
			$input = substr($input, 0, 80).'......';
		}
		//输出
		if(!is_string($output)){
			$output = json_encode($output);
		}
		//输出的长度
		$maxOutput = 20000;
		if(defined('SERVERNAME') && SERVERNAME != 'dev' && SERVERNAME != 'local' && SERVERNAME != 'verify'){
			$maxOutput = 2000;
		}
		if(strlen($output) > $maxOutput){
			$output = substr($output, 0, $maxOutput).'...';
		}
		
		$message = array($service, $level_, $username, $sesionId, $ip, $callSource, $input, $output);
		$message = $separator.join($separator, $message);
		
		$logName = $logName ? $logName : (defined('LOGNAME') ? LOGNAME : Configure::read('conf.log.logName'));
		parent::write($logName, $message);
	}
	
	/**
	 * 取给定时间与当前时间的时间差，毫秒
	 */
	function lt($sTime, $now = false){
		if(!$now) $now = explode(' ', microtime());
		return number_format((($now[1] - $sTime[1]) + ($now[0] - $sTime[0]))  * 1000, 3);
	}
	
	/** 用于记录上次当前调用距离上次时间$last有多久了，单位毫秒，顺便打印消息$message
	 * @param unknown_type $message
	 * @param unknown_type $last
	 * @return mixed
	 */
	public function cost($message=false, $last=false){
		$now = microtime(true);
		if(!$message){
			return $now;
		}
		if(!$last){
			NLog::w($message);
		}else{
			$timeCost = ($now - $last) * 1000;
			$timeCost = number_format($timeCost, 2, '.', '');
			NLog::w($message.' timecost: '.$timeCost.'ms.');
		}
		return $now;
	}
}



