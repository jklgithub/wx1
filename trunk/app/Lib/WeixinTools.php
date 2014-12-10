<?php
App::uses('NLog', 'Lib');
/**
 * design as a tool, not a framework
 */
class WeixinTools
{
	const TYPE_NEW_MESSAGE = 'new_message';
	const TYPE_SUBSCRIBE = 'subscribe';
	const TYPE_UNSUBSCRIBE = 'unsubscribe';
	const TYPE_VALIDATE_URL = 'validate_url';
	const TYPE_SIGNATURE_ERROR = 'signature_error';
	const TYPE_UNKNOWN_ERROR = 'unknown_error';

	private $token = 'weixin';
	public $request_type = 'message';
	public $message = null;
	
	/**
	 * parse the request
	 * @param token 
	 * @param get $_GET
	 * @param raw_post $GLOBALS["HTTP_RAW_POST_DATA"]
	 */
	function __construct($token, $get, $raw_post)
	{
		$this->token = $token;
        //签名不合法
        if(!$this->checkSignature($get)){
        	$this->request_type = WeixinTools::TYPE_SIGNATURE_ERROR;
        }
		elseif (!empty($_GET["echostr"])) {
			$this->request_type = WeixinTools::TYPE_VALIDATE_URL;
		}
		elseif (!empty($raw_post)) {
			$this->message = new Message($raw_post);
			if($this->message->msg_type == Message::TYPE_TEXT and $this->message->content == 'Hello2BizUser') {
				$this->message->msgType = 999;
				$this->request_type = WeixinTools::TYPE_SUBSCRIBE;
			}
			elseif ($this->message->msg_type == Message::TYPE_EVENT and $this->message->event == 'unsubscribe') {
				$this->message->msgType = 998;
				$this->request_type = WeixinTools::TYPE_UNSUBSCRIBE;
			}
			elseif ($this->message->msg_type == Message::TYPE_EVENT and $this->message->event == 'subscribe') {
				$this->message->msgType = 999;
				$this->request_type = WeixinTools::TYPE_SUBSCRIBE;
			}
			else {
				$this->request_type = WeixinTools::TYPE_NEW_MESSAGE;
			}
		}
		else {
			$this->request_type = WeixinTools::TYPE_UNKNOWN_ERROR;
		}
		NLog::w('==========================================');
		NLog::w('token: '.$token);
		NLog::w('request type: '.$this->request_type);
		NLog::w("raw post: \n".$raw_post);
	}

	/**
	 * http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E7.BD.91.E5.9D.80.E6.8E.A5.E5.85.A5
	 */
	public function reply_valid()
    {
        return $_GET["echostr"];
    }
	
	/**
	 * reply a text message to user.
	 * http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E6.96.87.E6.9C.AC.E6.B6.88.E6.81.AF
	 */
	public function reply_text($text, &$replyInfo)
	{
	//	if($this->saveMsg($text, 1) == false && !$mustSend) return '';
		
		$time = time();
        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>0</FuncFlag>
</xml>";             
        $resultStr = sprintf($textTpl, $this->message->from_username, $this->message->to_username, $time, $text);
		NLog::w("reply_text: \n".$resultStr);
		$replyInfo['msgType']		= 1;
		$replyInfo['content']		= $text;
        return $resultStr;

	}
	
	/**
	 * reply a article message to user.
	 * http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E5.9B.9E.E5.A4.8D.E5.9B.BE.E6.96.87.E6.B6.88.E6.81.AF
	 */
	public function reply_article($title, $description, $pic_url, $url)
	{
	//	if($this->saveMsg($title.' '.$description.' ['.$pic_url.'] '.$url, 6) == false) return '';
		
		$time = time();
        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
 <Articles>
 <item>
 <Title><![CDATA[%s]]></Title> 
 <Description><![CDATA[%s]]></Description>
 <PicUrl><![CDATA[%s]]></PicUrl>
 <Url><![CDATA[%s]]></Url>
 </item>
 </Articles>
<FuncFlag>0</FuncFlag>
</xml>";             
        $resultStr = sprintf($textTpl, $this->message->from_username, $this->message->to_username, $time, $title, $description, $pic_url, $url);
		NLog::fw("weixin-reply", $resultStr);
        return $resultStr;

	}
	
	public function reply_article_tt($title, $description, $pic_url, $url, $toUser)
	{
	//	if($this->saveMsg($title.' '.$description.' ['.$pic_url.'] '.$url, 6) == false) return '';
		
		$time = time();
        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
 <Articles>
 <item>
 <Title><![CDATA[%s]]></Title> 
 <Description><![CDATA[%s]]></Description>
 <PicUrl><![CDATA[%s]]></PicUrl>
 <Url><![CDATA[%s]]></Url>
 </item>
 </Articles>
<FuncFlag>0</FuncFlag>
</xml>";             
        $resultStr = sprintf($textTpl, $toUser, $this->message->to_username, $time, $title, $description, $pic_url, $url);
		NLog::fw("weixin-reply", $resultStr);
        return $resultStr;

	}
	
	public function reply_text_article($description, $mustSend = false)//, $url)
	{
	//	if($this->saveMsg($description, 1)  == false && !$mustSend) return '';
		
		$time = time();
		$textTpl = "<xml>
 <ToUserName><![CDATA[%s]]></ToUserName>
 <FromUserName><![CDATA[%s]]></FromUserName>
 <CreateTime>%s</CreateTime>
 <MsgType><![CDATA[text]]></MsgType>
 <Content><![CDATA[%s]]></Content>
 <FuncFlag>0</FuncFlag>
 </xml>";            
        $resultStr = sprintf($textTpl, $this->message->from_username, $this->message->to_username, $time, $description);//, $url);
		NLog::fw("weixin-reply", $resultStr);
        return $resultStr;

	}
	
	public function reply_articles($data = false, &$replyInfo = false)
	{
		if(!$replyInfo) $replyInfo = array();
		if(!$data){
			$data = array(
				'list' => array(
					array(
						'picUrl' => 'http://v.dev.uboxol.com/assets/weixin_img/wx-banner.jpg',
						'title' => '快来加入我们吧',
				//		'description' => '注册友宝会员的2元，快来加入我们吧',	//发图的，description无效
						'url' => 'http://m.ubox.cn'
					),
					array(
						'picUrl' => 'http://img.ubox.cn/web/img/buyonline/select.png',
						'description' => '',
						'title' => '注册友宝会员，得2元',
						'url' => 'http://m.ubox.cn'
					),
					array(
						'picUrl' => 'http://img.ubox.cn/web/img/buyonline/select.png',
						'title' => '安装友宝客户端，得3元',
						'url' => 'http://m.ubox.cn',
					),
					array(
						'picUrl' => 'http://img.ubox.cn/web/img/buyonline/select.png',
						'title' => '在下方输入框里输入手机号，快速注册',
					),
				),
			);
		}
		$time 		= time();
		$items 		= '';
		$str		= '';
		$i			= 0;
		$cc			= 0;
		if(isset($data['list']) && count($data['list']) > 0){
			foreach($data['list'] as $d){
				if($d != false){
					$i++;
					$cc++;
					$items .= '<item>';
					if(isset($d['title']) && $d['title']){
						$items 	.= '<Title><![CDATA['.$d['title'].']]></Title>';
						$str 	.= $i.'. '.$d['title'];
					}
					if(isset($d['description']) && $d['description']){
						$items 	.= '<Description><![CDATA['.$d['description'].']]></Description>';
						$str	.= ' '.$d['description'];
					}
					if(isset($d['picUrl']) && $d['picUrl']){
						$items 	.= '<PicUrl><![CDATA['.$d['picUrl'].']]></PicUrl>';
						$str	.= $i == 1 ? ' '.$d['picUrl'] : '';
					}
					if(isset($d['url']) && $d['url']){
						$items 	.= '<Url><![CDATA['.$d['url'].']]></Url>';
						$str	.= ' '.$d['url'].'  ';
					}
					$items .= '</item>';
				}
			}
		}
		
	//	if($this->saveMsg($str, 6) == false && !$mustSend) return '';
		
        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
 <Articles>%s</Articles>
<FuncFlag>0</FuncFlag>
</xml>";  
		         
        $resultStr = sprintf($textTpl, $this->message->from_username, $this->message->to_username, $time, $cc, $items);
		NLog::fw("weixin-reply", $resultStr);
		$replyInfo['msgType']		= 6;
		$replyInfo['content']		= $resultStr;
        return $resultStr;
        /*
        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>4</ArticleCount>
 <Articles>
 
 <item>
 <Title><![CDATA[友宝自助便利店]]></Title> 
 <Description><![CDATA[注册友宝账号送饮料，手机购买打九折。http://m.ubox.cn]]></Description>
 <PicUrl><![CDATA[http://img.ubox.cn/web/img/res/banner-activity-gzdt-327.png]]></PicUrl>
 <Url><![CDATA[http://m.ubox.cn/]]></Url>
 </item>
 
 <item>
 <Title><![CDATA[现在注册友宝账号，立即送饮料]]></Title> 
 <Description><![CDATA[注册友宝账号送饮料，手机购买打九折。http://m.ubox.cn]]></Description>
 <PicUrl><![CDATA[http://tp4.sinaimg.cn/2705160047/180/5630141913/0]]></PicUrl>
 <Url><![CDATA[http://m.ubox.cn]]></Url>
 </item>
  <item>
 <Title><![CDATA[下载iPhone/iPad客户端]]></Title> 
 <Description><![CDATA[注册友宝账号送饮料，手机购买打九折。http://m.ubox.cn]]></Description>
 <PicUrl><![CDATA[http://weixiao001.com/ios.png]]></PicUrl>
 <Url><![CDATA[http://www.ubox.cn/lastest-version.php?ver=iphone&channel=HOME-IPHONE]]></Url>
 </item>
  <item>
 <Title><![CDATA[下载Android客户端]]></Title> 
 <Description><![CDATA[注册友宝账号送饮料，手机购买打九折。http://m.ubox.cn]]></Description>
 <PicUrl><![CDATA[http://weixiao001.com/android.png]]></PicUrl>
 <Url><![CDATA[http://www.ubox.cn/lastest-version.php?ver=android&channel=HOME-ANDROID]]></Url>
 </item>
  </Articles>
<FuncFlag>0</FuncFlag>
</xml>";             
        $resultStr = sprintf($textTpl, $this->message->from_username, $this->message->to_username, $time);
		NLog::w("reply_text: \n".$resultStr);
        return $resultStr;
*/
	}

	/**
	 * reply a music to user.
	 * http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E5.9B.9E.E5.A4.8D.E9.9F.B3.E4.B9.90.E6.B6.88.E6.81.AF
	 */
	public function reply_music($title, $description, $music_url, $hq_music_url)
	{
		$time = time();
        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
 <Music>
 <Title><![CDATA[%s]]></Title>
 <Description><![CDATA[%s]]></Description>
 <MusicUrl><![CDATA[%s]]></MusicUrl>
 <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
 </Music>
<FuncFlag>0</FuncFlag>
</xml>";             
        $resultStr = sprintf($textTpl, $this->message->from_username, $this->message->to_username, $time, $title, $description, $music_url, $hq_music_url);
		NLog::fw("weixin-reply", $resultStr);
        return $resultStr;

	}
	
	/**
	 * http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E7.BD.91.E5.9D.80.E6.8E.A5.E5.85.A5
	 */
	private function checkSignature($get)
	{
        $signature = $get["signature"];
        $timestamp = $get["timestamp"];
        $nonce = $get["nonce"];
        
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	//$type 消息类型 1:text文本 2:image图片 3:voice语音 4:video视频 5:music音乐 6:news图文信息 7:模板消息
/*	private function saveMsg($content, $type){
		$dialogModel = new WXDialogs();
		return $dialogModel->saveReply(trim($this->message->from_username), $content, $type);
	}*/
}

/**
 * http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E6.B6.88.E6.81.AF.E6.8E.A8.E9.80.81
 */
class Message{
	const TYPE_TEXT = 'text';
	const TYPE_IMAGE = 'image';
	const TYPE_LOCATION = 'location';
	const TYPE_LINK = 'link';
	const TYPE_EVENT = 'event';
	const TYPE_VOICE = 'voice';

	public $to_username 	= null;
	public $from_username 	= null;
	public $create_time 	= null;	
	public $msg_type 		= null;
	public $msg_id 			= null;
	public $content 		= null;
	public $pic_url 		= null;
	public $media_id 		= null;
	public $location_x 		= null;
	public $location_y 		= null;
	public $scale 			= null;
	public $label 			= null;
	public $title 			= null;
	public $description 	= null;
	public $url 			= null;
	public $event			= null;
	public $event_key 		= null;
	public $ticket	 		= null;
	public $precision		= null;
	public $msgType			= 1;//给日志的类型
	public $msgContent		= '';
	
	function __construct($raw_post) {
        $xml = simplexml_load_string($raw_post, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->from_username 	= $xml->FromUserName;
        $this->to_username 		= $xml->ToUserName;
		$this->create_time 		= $xml->CreateTime;
		$this->msg_type 		= $xml->MsgType;
		$this->msg_id 			= $xml->MsgId;
		
		//$type 消息类型 1:text文本 2:image图片 3:voice语音 4:video视频 5:music音乐 6:news图文信息  7 位置  8 事件 9 链接消息 11 微信自动上报位置
		$content				= '';
		$type					= 1;
		
		switch ($this->msg_type) {
			case Message::TYPE_TEXT:
				$this->content 	= trim($xml->Content);
				$content		= $this->content;
				$type			= 1;
				break;
			case Message::TYPE_IMAGE:
				$this->pic_url 	= trim($xml->PicUrl);
				$content		= $this->pic_url;
				$type			= 2;
				break;
			case Message::TYPE_VOICE:
				$this->media_id = trim($xml->MediaId);
				$content		= $this->media_id;
				$type			= 3;
				break;
			case Message::TYPE_LOCATION:
				$this->location_x = trim($xml->Location_X);
				$this->location_y = trim($xml->Location_Y);
				$this->scale 	= trim($xml->Scale);
				$this->label 	= trim($xml->Label);
				$content		= json_encode(array('scale'=>$this->scale, 'label'=>$this->label,'x'=>$this->location_x, 'y'=>$this->location_y));
				$type			= 7;
				break;
			case Message::TYPE_LINK:
				$this->description = trim($xml->Description);
				$this->title 	= trim($xml->Title);
				$this->url 		= trim($xml->Url);
				$content		= $this->description.' '.$this->title.'['.$this->url.']';
				$type			= 9;
				break;
			case Message::TYPE_EVENT:
				$this->event 	= trim($xml->Event);
				$this->event_key= trim($xml->EventKey);
				$this->ticket 	= isset($xml->Ticket) ? trim($xml->Ticket) : '';
				$content		= json_encode(array('event'=>$this->event, 'key'=>$this->event_key));
				$type			= 8;
				if($this->event == 'LOCATION'){
					$this->location_x 	= trim($xml->Latitude);
					$this->location_y 	= trim($xml->Longitude);
					$this->precision 	= trim($xml->Precision);//精度
					$type				= 11;
					$content			= json_encode(array('x' => $this->location_x, 'y' => $this->location_y, 'precision' => $this->precision));
				}
				break;
		}
		$this->msgType 			= $type;
		$this->msgContent		= $content;
		
	//	$dialogModel = new WXDialogs();
	//	$dialogModel->saveMsg(trim($this->from_username), $content, $type, 1);
	}
}

?>
