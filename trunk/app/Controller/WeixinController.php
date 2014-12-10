<?php 
App::uses('NLog', 'Lib');
App::uses('WeixinTools', 'Lib');
App::uses('WeixinUtil', 'Lib');
App::uses('Http', 'Lib');
/**
 * 
 */
class WeixinController extends AppController {

	public $name 	= 'Weixin';
	public $userId 	= false;
	public $uses 	= array();

	function beforeFilter(){
		parent::beforeFilter();
	}
/*******消息功能***********************************************************************************************************/
	public function index(){
	//	$this->test();
	//	exit;
		NLog::w('--------weixin-start---------');
		NLog::w('--------weixin-GET---------'.json_encode($this->request->query));
		$rawPost = defined('GLOBALS_HTTPRAWPOSTDATA') ? GLOBALS_HTTPRAWPOSTDATA : null;
		
		NLog::fw("weixin-reply", $rawPost);
		
		$tools 			= new WeixinTools(Configure::read('conf.weixin.wx-token'), $this->request->query, $rawPost);
		$weixinId		= trim($tools->message->from_username);
		
		switch ($tools->request_type) {
			case WeixinTools::TYPE_NEW_MESSAGE:
				//仅测试账号使用的功能测试功能
				$ret	=  $tools->reply_text("欢迎关注", $replyInfo);
				break;
			case WeixinTools::TYPE_SUBSCRIBE://新关注
				$ret	=  $tools->reply_text("欢迎关注", $replyInfo);
				break;
			case WeixinTools::TYPE_UNSUBSCRIBE:
				break;
			case WeixinTools::TYPE_VALIDATE_URL:
				$ret = $tools->reply_valid();
				break;
			default:
				echo 'Error Request';//, Info:<br/><br/>';
				break;
		}
		NLog::fw("weixin-reply", 'return:'.$ret);
		if($ret && $ret != '[no reply]'){
			echo $ret;
		}
		exit;
	}
}





