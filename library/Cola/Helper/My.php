<?php
class Cola_Helper_My
{
	protected static $ssourl = 'http://api.sso.letv.com/api/';
	
	/**
	 * JSONP输出
	 * @param  integer $code     
	 * @param  array   $data     
	 * @param  string  $callback 
	 * @return array            
	 */
	public static function output($code=200,$data=array(),$callback=''){
		if (empty($callback)) {
			$callback = isset($_REQUEST['callback'])?$_REQUEST['callback']:'';
		}
		$flag = preg_match('/^\w+$/',$callback);
		if(empty($callback) || !$flag){
			exit(json_encode(array('code'=>$code,'data'=>$data)));
		}else{
			exit($callback.'('.json_encode(array('code'=>$code,'data'=>$data)).')');
		}
	}

	/**
	 * curl http 请求
	 * @param string $destURL  
	 * @param string $paramStr 
	 * @param string $flag     
	 * @param string $name     
	 * @param string $password 
	 */
	public static function Curl($destURL, $paramStr='',$flag='get',$name='',$password=''){
	   if(!extension_loaded('curl')) exit('php_curl.dll');
		$curl = curl_init(); 
		if($flag=='post'){
			curl_setopt($curl, CURLOPT_POST, 1);               
			curl_setopt($curl, CURLOPT_POSTFIELDS, $paramStr); 
		}
		curl_setopt($curl, CURLOPT_URL, $destURL);    
		curl_setopt($curl, CURLOPT_TIMEOUT, 2);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($name) && !empty($password)){
			curl_setopt($curl, CURLOPT_USERPWD, "{$name}:{$password}");
		}
		$str = curl_exec($curl);
		curl_close($curl);
		return $str;
	}

	/**
	 * 获取IP地址
	 */
	public static function GetRealIp() {
        $pattern = '/(\d{1,3}\.){3}\d{1,3}/';
        if (isset($_SERVER["HTTP_CDN_SRC_IP"]) && !empty($_SERVER["HTTP_CDN_SRC_IP"])) {
            //获取cdn加速后的真实客户端ip
            return $_SERVER['HTTP_CDN_SRC_IP'];
        } else {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && preg_match_all($pattern, $_SERVER['HTTP_X_FORWARDED_FOR'], $mat)) {
                foreach ($mat[0] AS $ip) {
                    //得到第一个非内网的IP地址
                    if ((0 === strpos($ip, '192.168.')) || (0 === strpos($ip, '10.')) || (0 === strpos($ip, '172.16.'))) {
                        continue;
                    } else {
                        return $ip;
                    }
                }
                return $ip;
            } else {
                if (isset($_SERVER["HTTP_CLIENT_IP"]) && preg_match($pattern, $_SERVER["HTTP_CLIENT_IP"])) {
                    return $_SERVER["HTTP_CLIENT_IP"];
                } else {
                    return $_SERVER['REMOTE_ADDR'];
                }
            }
        }
    }
    
	/**
	 * 从sso_token从获取用户全部信息
	 * wiki:http://wiki.letv.cn/pages/viewpage.action?pageId=19771054
	 * wiki:http://wiki.letv.cn/pages/viewpage.action?pageId=21107100
	 */
	public static function CurrentUserBaseInfo(){
		$userinfo = array();
		$sso_tk = "";

		if(!empty($_REQUEST['sso_tk'])){
			//非web端（移动或tv）
			$sso_tk = trim($_REQUEST['sso_tk']);
		}elseif(!empty($_COOKIE['sso_tk'])){
			//web端，通过cookie走
			$sso_tk = trim($_COOKIE['sso_tk']);
		}elseif(!empty($_SERVER['HTTP_SSOTK'])){
			//移动端新方法
			$sso_tk = trim($_SERVER['HTTP_SSOTK']);
		}

		if(empty($sso_tk)){
			return $userinfo;
		}

		$tkinfo = self::Curl(self::$ssourl.'checkTicket/tk/'.$sso_tk.'/need_expire/1/?all=1');
		$tkinfo = json_decode($tkinfo,true);
		if(!empty($tkinfo['bean']['ssouid']) && !empty($tkinfo['expire'])){
			$userinfo = $tkinfo['bean'];
			$userinfo += self::getUserProfile($userinfo['uid']);
		}
		return $userinfo;
	}

	/**
	 * 获取用户扩展信息
	 * @param  int $uid
	 * @return array
	 * wiki:http://wiki.letv.cn/pages/viewpage.action?pageId=21107100  
	 */
	public static function getUserProfile($uid){
		$userinfo = array();
		$pinfo = self::Curl(self::$ssourl.'getUserProfileByID?uid='.$uid);
		$pinfo = json_decode($pinfo,true);
		if(!empty($pinfo['bean']['uid'])){
			$userinfo = $pinfo['bean'];
			$userinfo['score'] = self::UserCrditBalance($pinfo['bean']['uid']);
		}
		return $userinfo;
	}

	/**
	 * 由sso_token获取用户ID
	 * wiki:http://wiki.letv.cn/pages/viewpage.action?pageId=19771054
	 */
	public static function CurrentUserUid(){
		$user_id = $sso_tk = "";
		
		if(!empty($_REQUEST['sso_tk'])){
			//非web端（移动或tv）
			$sso_tk = trim($_REQUEST['sso_tk']);
		}elseif(!empty($_COOKIE['sso_tk'])){
			//web端，通过cookie走
			$sso_tk = trim($_COOKIE['sso_tk']);
		}elseif(!empty($_SERVER['HTTP_SSOTK'])){
			//移动端新方法
			$sso_tk = trim($_SERVER['HTTP_SSOTK']);
		}

		if(empty($sso_tk)){
			return $user_id;
		}
		$user_id = Cola_Com::cache('_cache')->get('usertoken:'.md5($sso_tk));
		if(empty($user_id)){
			$token_info = self::Curl(self::$ssourl.'checkTicket/tk/'.$sso_tk.'/need_expire/1/');
			$token_info = json_decode($token_info,true);
			$user_id = intval($token_info['bean']['result']);
			if(!empty($user_id) && !empty($token_info['expire'])){
				$expire = ($token_info['expire'] > 30*86400) ? 30*86400 : $token_info['expire'];
				Cola_Com::cache('_cache')->set('usertoken:'.md5($sso_tk),$user_id,$expire);
			}
		}
		return $user_id;
	}
	
	/**
	 * 用户ID获取用户基础信息
	 * @param int $uid 
	 * wiki:http://wiki.letv.cn/pages/viewpage.action?pageId=19771024
	 */
	public static function FullUserInfoByUid($uid){
		$userinfo = array();
		if(empty($uid)){
			return $userinfo;
		}
		$ssoinfo = self::Curl(self::$ssourl.'getUserByID/uid/'.$uid.'/dlevel/total');
		$ssoinfo = json_decode($ssoinfo,true);
		if(!empty($ssoinfo['bean']['uid'])){
			$userinfo = $ssoinfo['bean'];
		}
		return $userinfo;
	}

	/**
	 * 用户名获取用户基础信息
	 * @param int $uname 
	 * wiki:http://wiki.letv.cn/pages/viewpage.action?pageId=19771036
	 */
	public static function FullUserInfoByUname($uname){
		$userinfo = array();
		if(empty($uname)){
			return $userinfo;
		}
		$ssoinfo = self::Curl(self::$ssourl.'getUserByName/username/'.$uname);
		$ssoinfo = json_decode($ssoinfo,true);
		if(!empty($ssoinfo['bean']['uid'])){
			$userinfo = $ssoinfo['bean'];
		}
		return $userinfo;
	}
	
	/**
	 * 获取用户账户积分
	 * @param [type] $uid [description]
	 */
	public static function UserCrditBalance($uid){
		$balance = 0;
		if(empty($uid)){
			return $balance;
		}
		$result = self::Curl('http://api.my.letv.com/credit/balance?uid='.$uid);
		$result = json_decode($result,true);
		if(200 == $result['code']){
			$balance = intval($result['data']['credits']);
		}
		return $balance;
	}

	public static function GetMicroTime(){
		return microtime(true);
	}
}
