<?php
define("TIME_FORMAT_MINITE", "%s分钟前");
define("TIME_FORMAT_TODAY", "今天 %s");
define("TIME_FORMAT_YESTODAY", "昨天 %s");
define("TIME_FORMAT_HISTORY", "%s-%s-%s");
define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
define('TIME_FORMAT_CAPTION_TODAY','今天');
define('TIME_FORMAT_CAPTION_YESTODAY','昨天');
define('TIME_FORMAT_CAPTION_YEAR','年');
define('TIME_FORMAT_CAPTION_MONTH','月');
define('TIME_FORMAT_CAPTION_DAY','日');
define('TIME_FORMAT_CAPTION_HOUR','点');
define('TIME_FORMAT_CAPTION_MINITE','分');
define('TIME_FORMAT_CAPTION_SECOND','秒');
define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");

/**
 * 
 * 工具类（提供通用方法）
 * @author  other && gaojun
 * @copyright 2012 letv.com
 * @link www.letv.com
 * @version 1.0  date:2012-05-05
 */
class Common_Tools {
	
	/**
	 * 取得二维数组中某一列数据的一维数组
	 * @param array $array  要处理数组
	 * @param string|int $colName   字段名或索引值，默认第一列
	 * @return array 指定列的一维数组
	 */
	public static function getOneColumnOfArray($array, $colName = 0) {
		$col = null;
		if (is_array ( $array )) { //如果是数组则处理
			foreach ( $array as $val ) {
				if ($val [$colName]) { //有列数据则操作
					$col [] = $val [$colName];
				}
			}
		}
		return $col;
	}
	
	/**
	 * 
	 * url GET请求方法
	 * @param str $url
	 * @param bool $isPost
	 */
	public static function requestUrl($url, $isPost = false, $timeout=10) {
		$curl = curl_init ();
		if ($isPost) {
			$paramArr = explode ( '?', $url );
			if (empty ( $paramArr ))
				return false;
			$url = $paramArr [0];
			$dataArr = explode ( '&', $paramArr [1] );
			if (empty ( $dataArr ))
				return false;
			$postData = array ();
			foreach ( $dataArr as $val ) {
				$tmp = explode ( '=', $val );
				$postData [$tmp [0]] = $tmp [1];
			}
			curl_setopt ( $curl, CURLOPT_POST, 1 ); //是否是post传递
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $postData ); //设置POST提交的字符串
		}
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_HEADER, false ); //是否设置header
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上.
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		

		$output = curl_exec ( $curl );
		$info = curl_getinfo ( $curl );
		if ($output === false || $info ['http_code'] != 200) {
			return false;
		} else {
			return $output;
		}
	}
	
	//得到当前用户Ip地址
	public static function getRealIp() {
		$pattern = '/(\d{1,3}\.){3}\d{1,3}/';
		if (isset ( $_SERVER ["HTTP_X_FORWARDED_FOR"] ) && preg_match_all ( $pattern, $_SERVER ['HTTP_X_FORWARDED_FOR'], $mat )) {
			foreach ( $mat [0] as $ip ) {
				//得到第一个非内网的IP地址
				if ((0 != strpos ( $ip, '192.168.' )) && (0 != strpos ( $ip, '10.' )) && (0 != strpos ( $ip, '172.16.' ))) {
					return $ip;
				}
			}
			return $ip;
		} else {
			if (isset ( $_SERVER ["HTTP_CLIENT_IP"] ) && preg_match ( $pattern, $_SERVER ["HTTP_CLIENT_IP"] )) {
				return $_SERVER ["HTTP_CLIENT_IP"];
			} else {
				return $_SERVER ['REMOTE_ADDR'];
			}
		}
	}
	
	//得到无符号整数表示的ip地址
	public static function getIntIp() {
		return sprintf ( '%u', ip2long ( self::getRealIp () ) );
	}
	
	//文本入库前的过滤工作
	public static function getSafeText($textString, $htmlspecialchars = true) {
		return $htmlspecialchars ? htmlspecialchars ( trim ( strip_tags ( $textString ) ) ) : trim ( strip_tags ( $textString ) );
	}
	
	public static function msgRedirect($msg, $url = '', $seconds = 4) {
		if (! $url)
			$url = (isset ( $_SERVER ['HTTP_REFERER'] ) && $_SERVER ['HTTP_REFERER']) ? $_SERVER ['HTTP_REFERER'] : 'http://www.letv.com/';
		$time = $seconds * 1000;
		$charset = defined ( 'HTML_CHARSET' ) ? HTML_CHARSET : 'UTF-8';
		$html = <<<html
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
				<title>乐视提示您</title>
				<style type="text/css"><!--body{text-align:center; font-family: Arial, Helvetica, sans-serif; font-size:14px; padding-top:100px;}a,a:visited {color:\#0068b7;text-decoration:underline;}a:hover { color:\#0068b7;text-decoration:none;}div {width:334px; height:95px; padding-top:15px; background:url(http:\/\/i3.letvimg.com/img/201203/02/tishibg.png) no-repeat; padding-left:230px; margin:0 auto;}div ul { margin:0px; padding:0px; list-style:none; text-align:left; line-height:25px;}h1{ font-size:14px;margin:0px; padding:0px; color:\#eb610f;
		        }-->
				</style>
				</head>
				<body>
				<div>
				<ul>
				<li><h1>乐视提示您：</h1></li>
				<li>{$msg}</li>
				<li>如果您的浏览器没有自动跳转，请点<a href='javascript:location.href="{$url}"'>这里</a></li>
				</ul>
				</div>
				<script type="text/javascript">
				setTimeout('location.href="{$url}"',$time)
				</script>
				</body>
				</html>
html;
		self::outputExpireHeader ( - 86400 );
		echo $html;
		exit ();
	}
	
	//CURL请求
	public static function curl($destURL, $paramStr = '', $flag = 'get') {
		if (! extension_loaded ( 'curl' ))
			exit ( 'php_curl.dll' );
		$curl = curl_init ();
		if ($flag == 'post') { //post
			curl_setopt ( $curl, CURLOPT_POST, 1 );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $paramStr );
		}
		curl_setopt ( $curl, CURLOPT_URL, $destURL );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 5 );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$str = curl_exec ( $curl );
		curl_close ( $curl );
		return $str;
	}
	
	//生成一个17字节长唯一随机文件名
	public static function getRandNumber() {
		return chr ( mt_rand ( 97, 122 ) ) . mt_rand ( 10000, 99999 ) . time ();
	}
	
	/**
	 * 
	 * 发送邮件
	 * @param str $email
	 * @param str $msg 
	 * @return bool
	 */
	public static function sendMail($email, $mailTitle, $mailContent, $from = 'letv@letv-info.com', $fromName = '乐视网') {
		require_once PLUGIN_PATH . "phpmailer.php";
		$mail = new phpmailer ( true ); //New instance, with exceptions enabled
		

		$mailContent = eregi_replace ( "[\]", '', $mailContent );
		try {
			$mail->IsSMTP (); // tell the class to use SMTP
			$mail->CharSet = "UTF-8";
			$mail->SMTPAuth = true; // enable SMTP authentication
			//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
			$mail->Port = 25; // set the SMTP server port
			$mail->Host = "115.182.51.128"; // SMTP server mail.letv-info.com
			$mail->Username = "letv@letv-info.com"; // SMTP server username
			$mail->Password = "letv"; // SMTP server password
			$mail->From = $from;
			$mail->FromName = $fromName;
			$mail->AddAddress ( $email );
			$mail->Subject = $mailTitle;
			//$mail->AltBody    = $mailContent; // optional, comment out and test
			//$mail->WordWrap   = 80; // set word wrap
			$mail->MsgHTML ( $mailContent );
			//$mail->AddAttachment("images/phpmailer.gif");      //附件
			//$mail->IsSendmail(); 
			$mail->IsHTML ( true ); // send as HTML			
			$mail->Send ();
			return 1;
		} catch ( phpmailerException $e ) {
			//echo $e->errorMessage();
			return 0;
		}
	}
	
	/**
	 * 生成随机数/字符串
	 *
	 * @param int $length           长度
	 * @param boolean $numeric      是否为数字 false=字符串 true=数字
	 */
	public static function random($length, $numeric = false) {
		mt_srand ( ( double ) microtime () * 1000000 );
		if (( boolean ) $numeric) {
			$hash = sprintf ( '%0' . $length . 'd', mt_rand ( 0, pow ( 10, $length ) - 1 ) );
		} else {
			$hash = '';
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
			$max = strlen ( $chars ) - 1;
			for($i = 0; $i < $length; $i ++) {
				$hash .= $chars [mt_rand ( 0, $max )];
			}
		}
		return $hash;
	}
	
	/*
    * 截取字符串
    *
    * @param $string 要截取的字符串
    * @param $length 截取长度
    * @param $dot
    * @return 取得到的结果集
    */
	public static function cutstr($string, $length, $dot = ' ...', $charset = 'utf-8') {
		if (strlen ( $string ) <= $length) {
			return $string;
		}
		
		$string = str_replace ( array ('&amp;', '&quot;', '&lt;', '&gt;' ), array ('&', '"', '<', '>' ), $string );
		
		$strcut = '';
		if (strtolower ( $charset ) == 'utf-8') {
			$n = $tn = $noc = 0;
			while ( $n < strlen ( $string ) ) {
				
				$t = ord ( $string [$n] );
				if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1;
					$n ++;
					$noc ++;
				} elseif (194 <= $t && $t <= 223) {
					$tn = 2;
					$n += 2;
					$noc += 2;
				} elseif (224 <= $t && $t < 239) {
					$tn = 3;
					$n += 3;
					$noc += 2;
				} elseif (240 <= $t && $t <= 247) {
					$tn = 4;
					$n += 4;
					$noc += 2;
				} elseif (248 <= $t && $t <= 251) {
					$tn = 5;
					$n += 5;
					$noc += 2;
				} elseif ($t == 252 || $t == 253) {
					$tn = 6;
					$n += 6;
					$noc += 2;
				} else {
					$n ++;
				}
				
				if ($noc >= $length) {
					break;
				}
			
			}
			if ($noc > $length) {
				$n -= $tn;
			}
			
			$strcut = substr ( $string, 0, $n );
		
		} else {
			for($i = 0; $i < $length - 3; $i ++) {
				$strcut .= ord ( $string [$i] ) > 127 ? $string [$i] . $string [++ $i] : $string [$i];
			}
		}
		
		$strcut = str_replace ( array ('&', '"', '<', '>' ), array ('&amp;', '&quot;', '&lt;', '&gt;' ), $strcut );
		
		return $strcut . $dot;
	}
	
	/**
	 * 获取ip
	 * @return string
	 */
	public static function getIp() {
		if ($HTTP_SERVER_VARS ["HTTP_X_FORWARDED_FOR"]) {
			$ip = $HTTP_SERVER_VARS ["HTTP_X_FORWARDED_FOR"];
		} elseif ($HTTP_SERVER_VARS ["HTTP_CLIENT_IP"]) {
			$ip = $HTTP_SERVER_VARS ["HTTP_CLIENT_IP"];
		} elseif ($HTTP_SERVER_VARS ["REMOTE_ADDR"]) {
			$ip = $HTTP_SERVER_VARS ["REMOTE_ADDR"];
		} elseif (getenv ( "HTTP_X_FORWARDED_FOR" )) {
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		} elseif (getenv ( "HTTP_CLIENT_IP" )) {
			$ip = getenv ( "HTTP_CLIENT_IP" );
		} elseif (getenv ( "REMOTE_ADDR" )) {
			$ip = getenv ( "REMOTE_ADDR" );
		} else {
			$ip = "0.0.0.0";
		}
		return $ip;
	}
	
	/**
	 * 格式化时间
	 *
	 * @param time   $vtime        时间截
	 * @param str    $type         时间显示类型
	 * @param str    $lang         语言
	 * @return  str
	 */
	public static function lastvisittime($time) {
		$now = time();
		if (strpos($time,'-')!==false) {
			$time = strtotime($time);
		}
		if(($dur = $now - $time) < 3600) {
			$minutes = ceil($dur / 60);
			if ($minutes<=0){
				$minutes = 1;
			}
			$time = sprintf(TIME_FORMAT_MINITE, $minutes);
		}else
		if(date("Ymd", $now) == date("Ymd", $time)) {
			$time = sprintf(TIME_FORMAT_TODAY, date("H:i", $time));
		}else{
			if(date("Y") == date("Y",$time)){
				$time = sprintf(TIME_FORMAT_HISTORY_VISITOR,date("n",$time),date("j",$time)) . " " . date("H:i",$time);
			}else{
				$time = sprintf(TIME_FORMAT_HISTORY, date("Y",$time),date("n",$time),date("j",$time)) . " " . date("H:i",$time);
			}
		}
		return $time;
	}
	
	/*
    * 中间截取字符串
    *
    * @param $string 要截取的字符串
    * @param $leftCutLength 左侧截取长度
    * @param $rightCutLength 右侧截取长度
    * @param $dot 替换符号
    * @param $charset 字符编码法
    * @return 取得到的结果集
    */
	public static function cutStrByMiddle($string, $leftCutLength = 2, $rightCutLength = 2, $dot = '...', $charset = 'utf-8') {
		
		$strLen = strlen ( $string );
		if (! $leftCutLength || ! $rightCutLength || $strLen <= $leftCutLength || $strLen <= $rightCutLength || (($leftCutLength + $rightCutLength) >= $strLen)) {
			return $string;
		}
		
		$leftCutString = self::cutstr ( $string, $leftCutLength, '', $charset );
		$revString = self::mb_strrev ( $string );
		
		$rightCutString = self::cutstr ( $revString, $rightCutLength, '', $charset );
		$rightCutString = self::mb_strrev ( $rightCutString );
		
		$result = $leftCutString . $dot . $rightCutString;
		
		return $result;
	}
	
	/**
	 * 图片上传类
	 * state：1（上传成功）
	 *state：2（上传重复）
	 *state：3（上传文件格式不符合上传条件，拒上传）
	 *state：4（身份验证失败）
	 *state：5（上传文件大小不在允许上传范围之内）
	 *state：6（上传文件名含中文或者空格）
	 *state：7（文件上传失败）
	 *@param string $tmpfilename 服务器临时文件
	 *@param string $local_name 本地上传的文件名
	 */
	public static function uploadFile($tmpfilename, $local_name='') {
		if (file_exists ( $tmpfilename )) {
		        //扩展名
		        $ext_name = '.jpg';
		        if($local_name){
		              $tmp = explode('.', $local_name);
		              if(!empty($tmp[1]))
		                      $ext_name = '.'.$tmp[1];  
		        }
			$uploadfile = '/letv/data/upload/' . date ( 'ymdhis' ) . $ext_name;
			$url = 'http://upload.letvcdn.com:8000/single_upload_tool.php';
			if (move_uploaded_file ( $tmpfilename, $uploadfile )) {
				;
			} else {
				die ( 'Possible file upload attack!\n' );
			}
			$ch = curl_init ( $url );
			$postdata ['channel'] = 'user';
			$postdata ['username'] = 'lianshengdong';
			$postdata ['md5str'] = '6a7cbc3d823387112fcb6daf7104cc98';
			$postdata ['single_upload_submit'] = 'ok';
			$postdata ['type'] = 'mimetype';
			$postdata ['compress'] = 85; //图片压缩
			$postdata ['single_upload_file'] = '@' . $uploadfile; //文件名前必须加@
			

			curl_setopt ( $ch, CURLOPT_POST, true );
			curl_setopt ( $ch, CURLOPT_HEADER, false );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $ch, CURLOPT_TIMEOUT, 240 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postdata );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, array ('Expect: ' ) );
			$result = curl_exec ( $ch );
			curl_close ( $ch );
			return json_decode ( $result );
		} else {
			die ( "File not exists!\n" );
		}
	}
	
	/**
	 * 
	 * 写日志文件
	 * @param string $filename 日志文件名
	 * @param string $loginfo 日志内容
	 * @param string $path 路径
	 * 
	 */
	public static function logfile($filename, $loginfo, $path = '/tmp/') {
		$fp = fopen ( $path . $filename . '.log', 'a+' );
		fwrite ( $fp, "[" . date ( "Ymd H:i:s" ) . "] " . preg_replace ( '/[\r\n]/', '', $loginfo ) . "\r\n" );
		fclose ( $fp );
	}
	/**
	 * 通过sso.letv.com登录
	 * @param array $info['username'],$info['ssouid']; passportuid
	 * session 是序列化数组值，后续数据本地化
	 */
	public static function isLogin() {
		session_start ();
		$info = array ();
		$sso_tk = isset ( $_COOKIE ['sso_tk'] ) ? $_COOKIE ['sso_tk'] : ''; //获取cookie中sso_tk的值
		if (! empty ( $sso_tk )) {
			//以cookie中sso_tk的值为session的key是否存在
			if (isset ( $_SESSION [$sso_tk] )) {
				return $_SESSION[$sso_tk]; //存在直接返回，不用走接口验证,起到优化作用
			} else {
				//不存在，走接口验证并写入session
				$sso_tkInfo = self::requestUrl ( "http://api.sso.letv.com/api/checkTicket/tk/{$sso_tk}?all" );
				$sso_tkInfo = json_decode ( $sso_tkInfo, true );
				if ($sso_tkInfo['status'] == '1' && isset($sso_tkInfo ['bean']['ssouid'],$sso_tkInfo ['bean']['username'])){
					$info ['ssouid']   = $sso_tkInfo ['bean']['ssouid']; //验证成功返回用户ID
					$info ['username'] = $sso_tkInfo ['bean']['username']; //验证成功返回用户名
					$_SESSION [$sso_tk] = $info; //存入session
					return $info; //已登录
				} else {
					return $info; //未登录
				}
			}
		} else { //cookie中sso_tk不存在
			setcookie("u", '',-86400 * 365,"/",".letv.com");
			setcookie("ui", '',-86400 * 365,"/",".letv.com");
			setcookie("sso_tk", '',-86400 * 365,"/",".letv.com");
			return $info; //未登录
		}
	}
	/**
	 * 根据paassportID获取用户信息
	 */
	public static function getUserInfoByUid($uid) {
		$userInfo = array ();
		$url = "http://api.sso.letv.com/api/getUserByID/uid/{$uid}";
		$userInfo = json_decode ( self::requestUrl ( $url ), true );
		if ($userInfo ['status'] == '1' && isset ( $userInfo ['bean'] ) && ! empty ( $userInfo ['bean'] )) {
			return $userInfo ['bean'];
		} else {
			return array ();
		}
	}
	/**
	 * 根据用户名获取用户信息
	 */
	public static function getUserInfoByUsername($username) {
		$userInfo = array ();
		$url = "http://api.sso.letv.com/api/getUserByName/username/" . $username;
		$userInfo = json_decode ( self::requestUrl ( $url ), true );
		if ($userInfo ['status'] == '1' && isset ( $userInfo ['bean'] ) && ! empty ( $userInfo ['bean'] )) {
			return $userInfo ['bean'];
		} else {
			return array ();
		}
	}
	
	/*
		|----------------------------------------------------------------------------
		| 字符串加密与解密函数            来源ucenter
		  $string	原文或者密文
		  $operation	操作(ENCODE | DECODE), 默认为 DECODE解密
		  $key		密钥
		  $expiry		密文有效期, 加密时候用的， 单位 秒，0 为永久有效
		  return   处理后的 原文或者 经过 base64_encode 处理后的密文,如果失效，返回空
		  如：
				$a = authcode('abc', 'ENCODE', 'key');//加密
				$b = authcode($a, 'DECODE', 'key');  // $b(abc)
				$a = authcode('abc', 'ENCODE', 'key', 3600);//加密
				$b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
		|----------------------------------------------------------------------------
		|
		*/
	public static function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4; //note 随机密钥长度 取值 0-32;
		//note 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
		//note 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
		//note 当此值为 0 时，则不产生随机密钥
		$key = md5 ( $key );
		$keya = md5 ( substr ( $key, 0, 16 ) );
		$keyb = md5 ( substr ( $key, 16, 16 ) );
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( microtime () ), - $ckey_length )) : '';
		
		$cryptkey = $keya . md5 ( $keya . $keyc );
		$key_length = strlen ( $cryptkey );
		
		$string = $operation == 'DECODE' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
		$string_length = strlen ( $string );
		
		$result = '';
		$box = range ( 0, 255 );
		
		$rndkey = array ();
		for($i = 0; $i <= 255; $i ++) {
			$rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
		}
		for($j = $i = 0; $i < 256; $i ++) {
			$j = ($j + $box [$i] + $rndkey [$i]) % 256;
			$tmp = $box [$i];
			$box [$i] = $box [$j];
			$box [$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i ++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box [$a]) % 256;
			$tmp = $box [$a];
			$box [$a] = $box [$j];
			$box [$j] = $tmp;
			$result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
		}
		if ($operation == 'DECODE') {
			if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
				return substr ( $result, 26 );
			} else {
				return '';
			}
		} else {
			return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
		}
	}
	
	/**
	 * 时间显示
	 *
	 * @param string $timeLast
	 * @param string $timeNext
	 * @return string
	 */
	public static function getTimeOver($timeLast, $timeNext=0) {
		if (!$timeNext) {
			$timeNext = time();
		}
		if ($timeLast === false || $timeNext === false || $timeLast > $timeNext) {
			return "时间异常";
		}

		$iAll = (int)(($timeNext - $timeLast) / 60);

		if ($iAll < 60) {
			$iAll = $iAll==0?1:$iAll;
			return "{$iAll} 分钟前";
		}
		$hAll = (int)($iAll / 60);
		if ($hAll < 24) {
			return "{$hAll} 小时前";
		}
		$dAll = (int)($hAll / 24);
		if ($dAll < 30) {
			return "{$dAll} 天前";
		}
		if ($dAll < 365) {
			$m = (int)($dAll / 30);
			return "{$m} 月前";
		}
		if ($dAll >= 365) {
			return "1年前";
		}
		return date('Y-m-d', $timeLast);
	}
	//全角 => 半角
	public static function qj2bj($string) {
		$convert_table = Array(
		'０' => '0','１' => '1','２' => '2','３' => '3','４' => '4','５' => '5','６' => '6','７' => '7','８' => '8','９' => '9',
		'Ａ' => 'A','Ｂ' => 'B','Ｃ' => 'C','Ｄ' => 'D','Ｅ' => 'E','Ｆ' => 'F','Ｇ' => 'G','Ｈ' => 'H','Ｉ' => 'I','Ｊ' => 'J','Ｋ' => 'K','Ｌ' => 'L',	'Ｍ' => 'M','Ｎ' => 'N','Ｏ' => 'O','Ｐ' => 'P','Ｑ' => 'Q','Ｒ' => 'R','Ｓ' => 'S','Ｔ' => 'T','Ｕ' => 'U','Ｖ' => 'V','Ｗ' => 'W','Ｘ' => 'X','Ｙ' => 'Y',	'Ｚ' => 'Z',
		'ａ' => 'a','ｂ' => 'b','ｃ' => 'c','ｄ' => 'd','ｅ' => 'e','ｆ' => 'f','ｇ' => 'g','ｈ' => 'h','ｉ' => 'i','ｊ' => 'j','ｋ' => 'k','ｌ' => 'l','ｍ' =>'m','ｎ' => 'n','ｏ' => 'o','ｐ' => 'p','ｑ' => 'q','ｒ' => 'r',	'ｓ' => 's','ｔ' => 't','ｕ' => 'u','ｖ' => 'v','ｗ' => 'w','ｘ' => 'x','ｙ' => 'y','ｚ' => 'z',
		'　' => ' ',
		'：' => ':',
		'。' => '.',
		'？' => '?',
		'，' => ',',
		'／' => '/',
		'；' => ';',
		'［' => '[',
		'］' => ']',
		'｜' => '|',
		'＃' => '#',
		'——' => '-',
		'、' => '',
		'‘' => '\'',
		'“' => '"',
		'【'=>'[',
		'】'=>']',
		'｛'=>'{',
		'｝'=>'}',
		'’'=>'\'',
		'＼'=>'\\',
		'～'=>'~',	
		'！'=>'!',	
		'＠'=>'@',	
		'￥'=>'$',	
		'％'=>'%',	
		'……'=>'...',	
		'＆'=>'&',	
		'×'=>'*',	
		'（'=>'(',	
		'）'=>')',		
		'＋'=>'+',	
		'＝'=>'=',	
		'·'=>'.',	
		'－'=>'-',
		);
		return strtr($string, $convert_table);
	}
	
	public static function get_hash_table($table,$code,$s=100){
		
		$hash1 = intval(fmod($code, $s));
		return $table."_".$hash1;
	}
}