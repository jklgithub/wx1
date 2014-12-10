<?php 
App::uses('Cache', 			'Core');
App::uses('NLog', 			'Lib');
App::uses('WeixinUtil', 	'Lib');
App::uses('Http', 			'Lib');
class WeixinUtil{
	public static $msgRightIcon = 'http://42.62.67.206/img/btn-right.png';
	
	public static function getAccessToken(){
		$key 		= 'WEIXIN_ACCESS_TOKEN';
		$tokenArr 	= Cache::read($key);
		NLog::w("--cache--getWeixinAccessToken---".json_encode($tokenArr));
		if(!$tokenArr || time() - $tokenArr['time'] > 1800){
			$url 	= "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".Configure::read('kms.wx.app_id')."&secret=".Configure::read('kms.wx.appsecret');
			$r 		= Http::httpsGet($url);
			NLog::w("--http--getWeixinAccessToken---".$url."----return:".$r);
			$r 		= json_decode($r, true);
			$token 	= isset($r['access_token']) ? urldecode($r['access_token']) : "";
			$tokenArr = array(
				'token' 	=> $token,
				'time' 		=> time(),
			);
			Cache::write($key, $tokenArr);
		}
		return $tokenArr['token'];
	}

	public static function appendUrl($url, $u1 = false, $u2 = false, $u3 = false, $u4 = false, $u5 = false){
		$u1 ? ($url .= '/'.$u1) : '';
		$u2 ? ($url .= '/'.$u2) : '';
		$u3 ? ($url .= '/'.$u3) : '';
		$u4 ? ($url .= '/'.$u4) : '';
		$u5 ? ($url .= '/'.$u5) : '';
		return $url;
	}

	//跳转到微信的登录url
	//array('weixin_buy', 'vm_list', 'search')
	public function toWeixinAuthUrl($us, $info = false, $gets = false){
		$scope 		= $info ? 'snsapi_userinfo' : 'snsapi_base';
		$redirect 	= 'http://'.Configure::read('kms.host').'/weixin/auth_redirect';
		if(is_array($us)){
			$redirect 	.= self::appendUrl($redirect, $us[0], isset($us[1]) ? $us[1] : false, isset($us[2]) ? $us[2] : false, isset($us[3]) ? $us[3] : false);
		}else{
			$redirect	.= $us;
		}
		if(is_array($gets)) unset($gets['url']);
		if(count($gets) > 0) $redirect	.= '?'.http_build_query($gets);
		
		$redirect = urlencode($redirect);
		
		$url = 'http://open.weixin.qq.com/connect/oauth2/authorize?appid='.
				Configure::read('kms.wx.app_id').'&redirect_uri='.
				$redirect.'&response_type=code&scope='.$scope.'&state=kms#wechat_redirect';
		return $url;
	}

	public static function showMsgPage($msg){
		NLog::w('error--msg--page:'.$msg);
		return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=0" name="viewport"> <title>error</title></head><body>'.$msg."</body></html>";
	}
	
	public static function qaConfig($k){
		$r = array(
			'qa_201412'	=> array(
				'input_key'	=> array('BTN_QA_2014'),
				'qa_name'	=> 'qa_201412',
				'str_start'	=> "亲 ，什么？ 
没抢到发动机模型，小钢炮音响？ 	
“福康同芯行 关注有好礼”第二波开始啦！ 	
礼品比第一波还丰厚哦，快来答题！ 
5道题全部答对，圣诞老人就带着幸运礼物来了！
			
",
				'str_end'	=> "只需发送––“福康同芯行，好礼送不停”＋您的姓名＋您的联系方式––就有机会获得我们的圣诞大礼包哦！",
			)
		);
		foreach($r as $k1 => $a){
			if($k == $k1 || in_array($k, $a['input_key'])){
				return $a;
			}
		}
		return false;
	}
}



