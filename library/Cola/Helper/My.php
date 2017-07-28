<?php
class Cola_Helper_My
{
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
   
	public static function GetMicroTime(){
		return microtime(true);
	}
}
