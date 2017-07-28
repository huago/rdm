<?php
/**
 * syslog-ng日志类 
 *
 * @author         guanbaolong <guanbaolong@letv.com>
 * @version        1.0
 * @example 
         //使用方法：
        Cola::loadClass('Common_SyslogNg' , LIBRARY_PATH);               //载入
        $cls_syslog = new Common_SyslogNg();                             //实例化
        $cls_syslog->property['datetime'] = date("Y-m-d H:i:s");         //设置日志时间
        $cls_syslog->property['clientip'] = $_SERVER['REMODE_ADDR'];     //设置IP
        $cls_syslog->property['operator'] = 'guanbaolong';               //设置操作者
        $cls_syslog->property['source']   = 'test.php';                  //设置来源
        $cls_syslog->property['message']  = 'Message for test!';         //设置日志内容
        $cls_syslog->log('sso', 'login');                                //写日志（项目，行为）
 */
 
define("SYSLOG_DATA_DELIMITER",'|');        // 日志内容分隔符
define("SYSLOG_DATA_REPLACEMENT",'&#30;');  // 含有分割符则替换成 &#30; 控制字符record separator的HTML Entity Code，原始代码中用的是 $#30;
 
class Common_SyslogNg
{
    /**
     * 属性列表
     *
     * @var array
     */
    public $property = array(
        'datetime'    =>    NULL,//日期
        'clientip'    =>    NULL,//客户端IP
        'operator'    =>    NULL,//操作者
        'action'    =>    NULL,//行为
        'source'    =>    NULL,//操作来源
        'message'    =>    NULL,//自定义log内容
    );
     
    /**
     * 属性分隔符
     *
     * @var string
     */
    private $delimiter = "\t";
     
    public function __construct(){
	$this->property['datetime'] = date('Y-m-d H:i:s');
    }
     
    /**
     * 记录日志
     *
     * @param string $product 项目
     * @param string $action  行为
     * 
     * @return bool
     */
    public function log($product , $action){
        $this->property['action'] = $action;
         
        $data = array(
            'action' => $this->property['action'],
        );
        $data['detail'] = $this->_buildLogString();
 
        return $this->syslog_ng($product , $data);
    }
 
    /**
     * 格式化日志
     *
     */
    protected function _buildLogString(){
        $returnLog = implode($this->delimiter, $this->property);
        return $returnLog;
    }
     
    /**
     * 通知syslog-ng
     *
     * @param string $product 项目
     * @param array $data     
     * @return bool
     */
    private function syslog_ng($product , $data) {
        $timeout = 1;
        $socket = @stream_socket_client('unix:///var/run/php-syslog-ng.sock', $errorno, $errorstr, $timeout);
        $msg = $this->_build_string($data);
        $msg = '<182>' . $product . '[' . getmypid() . ']:' . $msg; // 22*8+6, 22=local6, 6=info
        if ($socket == false) {
            if (is_dir("/letv/logs")) {
                file_put_contents("/letv/logs/phpmessages", $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
            }else if (is_dir("/stor/logs")) {
                file_put_contents("/stor/logs/phpmessages", $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
            
            return true;
        }
        fwrite($socket, $msg);
        fclose($socket);
        return true;
    }
     
    /**
     * 构建字符串
     *
     * @param array $data
     * @return string
     */
    private function _build_string(&$data) {
        $msg = "";
        $data['detail'] = str_replace(SYSLOG_DATA_DELIMITER, SYSLOG_DATA_REPLACEMENT, $data['detail']);
        $msg = $data['action'] . SYSLOG_DATA_DELIMITER. $data['detail'];
        return $msg;
    }
}
