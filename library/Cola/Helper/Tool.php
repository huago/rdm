<?php

define("TIME_FORMAT_MINITE", "%s分钟前");
define("TIME_FORMAT_TODAY", "今天 %s");
define("TIME_FORMAT_YESTODAY", "昨天 %s");
define("TIME_FORMAT_HISTORY", "%s-%s-%s");
define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
define('TIME_FORMAT_CAPTION_TODAY', '今天');
define('TIME_FORMAT_CAPTION_YESTODAY', '昨天');
define('TIME_FORMAT_CAPTION_YEAR', '年');
define('TIME_FORMAT_CAPTION_MONTH', '月');
define('TIME_FORMAT_CAPTION_DAY', '日');
define('TIME_FORMAT_CAPTION_HOUR', '点');
define('TIME_FORMAT_CAPTION_MINITE', '分');
define('TIME_FORMAT_CAPTION_SECOND', '秒');
define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");

/**
 * 工具类
 *
 * @example Cola_Helper_Tool::Curl
 *
 * @author guanbaolong
 * @version V2.0 2013-5-28
 * @copyright 
 *
 */
class Cola_Helper_Tool {

    /**
     * 时间显示
     *
     * @param string $timeLast
     * @param string $timeNext
     * @return string
     */
    public static function getTimeOver($timeLast, $timeNext = 0) {
        if (!$timeNext) {
            $timeNext = time();
        }
        if ($timeLast === false || $timeNext === false || $timeLast > $timeNext) {
            return "时间异常";
        }

        $iAll = (int) (($timeNext - $timeLast) / 60);

        if ($iAll < 60) {
            $iAll = $iAll == 0 ? 1 : $iAll;
            return "{$iAll}分钟前";
        }
        $hAll = (int) ($iAll / 60);
        if ($hAll < 24) {
            return "{$hAll}小时前";
        }
        $dAll = (int) ($hAll / 24);
        if ($dAll < 30) {
            return "{$dAll}天前";
        }
        if ($dAll < 365) {
            $m = (int) ($dAll / 30);
            return "{$m}月前";
        }
        return date('Y-m-d', $timeLast);
    }

    /**
     *
     * curl 方法
     * @param int $destURL		请求地址
     * @param int $timeout		超时时间
     * @param array $paramStr	请求参数
     * @param string $flag		请求方式  get/post
     * @param array $name		用户名
     * @param array $password	密码
     * @return string
     */
    public static function Curl($destURL, $timeout = 0, $paramStr = '', $flag = 'get', $name = '', $password = '') {
        $t1 = gettimeofday();
        //$curl = curl_init();
        //改用@qinlei提供的curl
        $curl_config = Cola::config('curl_config');
        if (cola::reg('__useCurlReason')) {
            $curl_config['logUserData'] = array('reason' => cola::reg('__useCurlReason'));
        } else {
            $curl_config['logUserData'] = array('reason' => 'unknown');
        }
        $_objCurl = new Cola_Com_Curl($curl_config);
        $curl = $_objCurl->curl;

        if ($flag == 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $paramStr);
        }
        curl_setopt($curl, CURLOPT_URL, $destURL);
        if ($timeout)
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($name) && !empty($password)) {
            curl_setopt($curl, CURLOPT_USERPWD, "{$name}:{$password}");
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //抓取302跳转后的内容
        curl_setopt($curl, CURLOPT_ENCODING, "gzip");

        $_objCurl->exec();
        $result = $_objCurl->response;
        //$result = curl_exec($curl);
        $info = curl_getinfo($curl);
        $curlErrno = curl_errno($curl);

        curl_close($curl);
        $t2 = gettimeofday();
        if ($info['http_code'] == 200 && $curlErrno == 0) {
            return $result;
        } else {
            //计算耗时，毫秒
            $time = ($t2['sec'] - $t1['sec']) * 1000 + ($t2['usec'] - $t1['usec']) / 1000;
            //记日志
            $message = "URL:$destURL    METHOD:$flag    PARAM:$paramStr    time:$time    ERROR:$curlErrno    HTTP_CODE:{$info['http_code']}";
            $cls_syslog = new Cola_Com_Syslogng();
            $cls_syslog->property['message'] = $message;
            $cls_syslog->log('msite', 'badrequest');
            return false;
        }
    }

    /**
     * 得到当前用户Ip地址
     *
     * @return ip地址
     */
    public static function getRealIp() {
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
     * 转换cid
     * @param int $cid
     * @param string $type  ptv|vrs  传入的cid类型
     * @param int $result	 传入ptv cid 返回对应的vrs cid ;  传入vrs cid 返回对应的ptv cid。
     */
    public static function transferCid($cid, $type = 'ptv') {
        //cid数组，默认以ptv cid 为key
        $cidArr = array(
            '1' => 4, //电影
            '2' => 5, //电视剧
            '3' => 86, //娱乐
            '4' => 221, //体育
            '5' => 6, //动漫
            '8' => 0, //其他
            '9' => 66, //音乐
            '11' => 78, //综艺
            '12' => 12, //科教
            '13' => 13, //生活
            '14' => 169, //汽车
            '15' => 15, //电视节目
            '16' => 111, //纪录片
            '17' => 92, //公开课
            '19' => 164, //乐视制造
            '20' => 186, //风尚
            '21' => 202, //乐视出品
            '22' => 298, //财经
            '23' => 307, //旅游
        );
        'vrs' == $type && $cidArr = array_flip($cidArr);
        return isset($cidArr[$cid]) ? $cidArr[$cid] : 0;
    }

    /**
     * 取vrs频道信息
     * @param int $cid
     * @return array
     */
    public static function getCategoryInfo($cid) {
        $result = '';
        $categoryArr = array(
            '4' => array('cname' => '电影', 'cid' => '4', 'dt' => '1'),
            '5' => array('cname' => '电视剧', 'cid' => '5', 'dt' => '1'),
            '6' => array('cname' => '动漫', 'cid' => '6', 'dt' => '1'),
            '86' => array('cname' => '娱乐', 'cid' => '86', 'dt' => '2'),
            '66' => array('cname' => '音乐', 'cid' => '66', 'dt' => '2'),
            '78' => array('cname' => '综艺', 'cid' => '78', 'dt' => '2'),
            '164' => array('cname' => '乐视制造', 'cid' => '164', 'dt' => '2'),
            '202' => array('cname' => '乐视出品', 'cid' => '202', 'dt' => '2'),
            '13' => array('cname' => '生活', 'cid' => '13', 'dt' => '2'),
            '15' => array('cname' => '电视节目', 'cid' => '15', 'dt' => '2'),
            '169' => array('cname' => '汽车', 'cid' => '169', 'dt' => '2'),
            '111' => array('cname' => '纪录片', 'cid' => '111', 'dt' => '2'),
            '92' => array('cname' => '公开课', 'cid' => '92', 'dt' => '2'),
            '221' => array('cname' => '体育', 'cid' => '221', 'dt' => '2'),
            '186' => array('cname' => '风尚', 'cid' => '186', 'dt' => '2'),
            '298' => array('cname' => '财经', 'cid' => '298', 'dt' => '2'),
            '307' => array('cname' => '旅游', 'cid' => '307', 'dt' => '2'),
            '0' => array('cname' => '其他', 'cid' => '0', 'dt' => '2'),
        );
        $result = isset($categoryArr[$cid]) ? $categoryArr[$cid] : $categoryArr[0];
        return $result;
    }

    /**
     * 获得客户端的操作系统
     *
     * @return string
     */
    public static function getClientOs() {
        $result = '';
        if (!isset($_SERVER['HTTP_USER_AGENT']))
            return 'Unknown';
        if (preg_match('/win/i', $_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], '95')) {
            $result = 'Windows 95';
        } else if (preg_match('/win 9x/i', $_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], '4.90')) {
            $result = 'Windows ME';
        } else if (preg_match('/win/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/98/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'Windows 98';
        } else if (preg_match('/win/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/nt 5.1/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'Windows XP';
        } else if (preg_match('/win/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/nt 5/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'Windows 2000';
        } else if (preg_match('/win/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/nt/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'Windows NT';
        } else if (preg_match('/win/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/32/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'Windows 32';
        } else if (preg_match('/sun/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/os/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'SunOS';
        } else if (preg_match('/ibm/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/os/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'IBM OS/2';
        } else if (preg_match('/Macintosh/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'Macintosh';
        } else if (preg_match('/iPhone/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'iPhone';
        } else if (preg_match('/iPod/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'iPod';
        } else if (preg_match('/iPad/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'iPad';
        } else if (preg_match('/Android/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'Android';
        } else if (preg_match('/PowerPC/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'PowerPC';
        } else if (preg_match('/AIX/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'AIX';
        } else if (preg_match('/HPUX/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'HPUX';
        } else if (preg_match('/NetBSD/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'NetBSD';
        } else if (preg_match('/BSD/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'BSD';
        } else if (preg_match('/OSF1/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = '/OSF1';
        } else if (preg_match('/IRIX/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'IRIX';
        } else if (preg_match('/FreeBSD/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'FreeBSD';
        } else if (preg_match('/teleport/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'teleport';
        } else if (preg_match('/flashget/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'flashget';
        } else if (preg_match('/webzip/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'webzip';
        } else if (preg_match('/offline/i', $_SERVER['HTTP_USER_AGENT'])) {
            $result = 'offline';
        } else {
            $result = 'Unknown';
        }
        return $result;
    }

    /**
     * 秒数 转化为 播放时长
     * @param int $sec
     * @return string
     */
    public static function sec2time($sec) {
        $result = '';
        $hour = sprintf("%02d", ($sec / 3600));
        $min = sprintf("%02d", $sec / 60 % 60);
        $second = sprintf("%02d", $sec % 60);
        $result .= intval($hour) > 0 ? $hour . ':' : '';
        $result .= $min . ':' . $second;
        return $result;
    }

    /**
     * utf-8中文截取，单字节截取模式
     * @param string $str
     * @param int $length
     * @param string $append
     * @param int $start
     * @return string
     */
    public static function cn_substr_utf8($str, $length, $append = '..', $start = 0) {
        if (strlen($str) < $start + 1) {
            return '';
        }
        preg_match_all("/./su", $str, $ar);
        $str2 = '';
        $tstr = '';
        for ($i = 0; isset($ar[0][$i]); $i++) {
            if (strlen($tstr) < $start) {
                $tstr.=$ar[0][$i];
            } else {
                if (strlen($str2) < $length + strlen($ar[0][$i])) {
                    $str2.=$ar[0][$i];
                } else {
                    break;
                }
            }
        }
        return $str == $str2 ? $str2 : $str2 . $append;
    }

    /**
     *
     * 输出
     * @param int $code			状态码
     * @param array $data		内容
     * @param string $callback	jsonp callback方法名
     * @return array
     */
    public static function output($code = 200, $data = array(), $callback = '') {
        if (empty($callback) || !preg_match('/^\w+$/', $callback)) {
            exit(json_encode(array('code' => $code, 'data' => $data)));
        } else {
            exit($callback . '(' . json_encode(array('code' => $code, 'data' => $data)) . ')');
        }
    }

    /**
     * 判断访问的终端类型
     */
    public static function getTerminal() {
        global $_SERVER;
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        $terminal = false;
        if (preg_match('/Googlebot|Baiduspider|EasouSpider|JikeSpider|Sosospider|YoudaoBot|Yahoo|sogou|MSNBot|bingbot|360spider|qqbrowser|ucweb|SymbianOS|SAMSUNG|Nokia/i', $agent) || (preg_match('/mobile/i', $agent) && !preg_match('/ipad/i', $agent)) //ipad 中会有mobile字样
                || preg_match('/android|iphone|ipod|playstation/i', $agent) || preg_match('/Windows Phone/i', $agent)) {
            $terminal = 'phone';
        } elseif (preg_match('/pad/i', $agent)) {
            $terminal = 'pad';
        } else {
            $terminal = 'pc';
        }
        return $terminal;
    }

    /*
     * 解析ini文件
     * ini文件格式为
     * a.b.c.d = true|On|Off
     * 解析结果为 $ini['a']['b']['c']['d'] = 1;
     *
     * @param string $iniFileName
     * @return array
     */

    public static function parseIniFile($iniFileName) {
        if (!file_exists($iniFileName) || !is_readable($iniFileName)) {
            return false;
        }

        $iniInfo = parse_ini_file($iniFileName);

        if (false === $iniInfo || empty($iniInfo)) {
            return false;
        }

        foreach ($iniInfo as $key => $value) {
            $keyArr = explode('.', $key);
            $lastKey = array_pop($keyArr);
            $cItem = &$iniInfo;

            if ($keyArr) {
                foreach ($keyArr as $tkey) {
                    if (!isset($cItem[$tkey])) {
                        $cItem[$tkey] = array();
                    }
                    //注意
                    $cItem = &$cItem[$tkey];
                }
            }
            $cItem[$lastKey] = $value;

            if (false !== strpos($key, '.'))
                unset($iniInfo[$key]);
        }

        return $iniInfo;
    }

}
