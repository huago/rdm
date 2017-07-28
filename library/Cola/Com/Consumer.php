<?php
class Cola_Com_Consumer{
    private $config = array();
    private $durable = true;
    private $mirror = false;
    private $conn = null;
    private $channel = null;
    private $queue = null;
    private $exchange = null; 
    private $exchange_types = array('direct'=>AMQP_EX_TYPE_DIRECT,'fanout'=>AMQP_EX_TYPE_FANOUT,'topic'=>AMQP_EX_TYPE_TOPIC);
  
    /** 
    * 创建连接、交换机、队列，并绑定 
    * @param array  $config RabbitMQ服务器信息 array('host'=>'127.0.0.1' , 'port'=> '5672', 'login'=>'guest' , 'password'=> 'guest','vhost' =>'/');
    * @param string $exchange_name 交换机名称 
    * @param mix    $routing_key_name 路邮键,可以是数组多路邮键绑定也可以是字符串
    * @param string $queue_name 队列名称
    * @param string $exchange_type 交换机类型 direct fanout topic
    * @param bool   $durable 队列是否持久化是否断开链接后自动删除
    * @param bool   $mirror 队列是否镜像
    * @param intval $prefetchcount 每次传输消息数
    * @return void  
    */
    public function __construct($config, $exchange_name, $routing_key_name, $queue_name, $exchange_type = 'direct', $durable = true, $mirror = false, $prefetchcount = 50){  
        if(!is_array($config) || empty($config) || empty($exchange_name) || empty($routing_key_name) || empty($queue_name) || empty($this->exchange_types[$exchange_type])){
            return false;
        }
        $this->config = $config;
        if (!self::connect()){
            return false;
        }
        $this->channel = new AMQPChannel($this->conn);
        if(!empty($prefetchcount)){
            $this->channel->setPrefetchCount($prefetchcount);
        }
        $this->durable = (bool)$durable;  
        $this->mirror = (bool)$mirror;  
        $this->declareExchange($exchange_name,$this->exchange_types[$exchange_type]);  
        $this->declareQueue($queue_name, $exchange_name, $routing_key_name);  
    }
      
    /** 
    * 创建连接
    * @return bool
    */
    private function connect(){
        try{
            $this->conn = new AMQPConnection($this->config);
            return $this->conn->connect();
        }catch(Exception $e){
            return false;
        }
    }
      
    /** 
    * 长链接无阻塞方式获取消息 
    * @param string $function_name 自定义处理消息的函数名称 
    * @param bool   $autoack 是否自动发送ACK应答，否则需要在自定义处理函数中手动发送 
    * @return bool  
    */
    public function run($function_name, $autoack = false){
        if(empty($function_name) || empty($this->exchange) || empty($this->queue)){
            return false;
        }
        if($autoack){
            $this->queue->consume($function_name, AMQP_AUTOACK);
        }else{
            $this->queue->consume($function_name);
        }
    }
     
    /** 
    * 异步方式获取消息 
    * @return mix
    */
    public function get($autoack = false){
        if(empty($this->exchange) || empty($this->queue)){
            return false;
        }
        try{
            if($autoack){
                $envelope = $this->queue->get(AMQP_AUTOACK);
                if(empty($envelope)){
                    return false;
                }else{
                    return $envelope->getBody();
                }
            }else{
                return $this->queue->get();
            }
        }catch(Exception $e){
            return false;
        }
    }
     
    /** 
    * 手动发送ack确认
    * @param string   $delivery_tag 消息的delivery_tag 
    * @return mix
    */
    public function ack($delivery_tag){
        if(empty($delivery_tag)){
            return false;
        }
        try{
            return $this->queue->ack($delivery_tag);
        }catch(Exception $e){
            return false;
        }
    }
     
    /** 
    * 创建交换机 
    * @param string $exchange_name 交换机名称 
    * @param string $exchange_type 交换机类型 
    * @return bool  
    */
    private function declareExchange($exchange_name, $exchange_type){
        try{
            $this->exchange = new AMQPExchange($this->channel);    
            $this->exchange -> setName($exchange_name);
            $this->exchange -> setType($exchange_type);
            $this->exchange -> setFlags(AMQP_DURABLE);
            $this->exchange->declare();
        }catch(Exception $e){
            $this->exchange = null;
        }
    }
      
    /** 
    * 创建队列和绑定
    * @param string $queue_name 队列名称 
    * @param string $exchange_name 交换机名称 
    * @param mix $routing_key_name 路邮键，可以是数组多路邮键绑定也可以是字符串
    * @return bool  
    */
    private function declareQueue($queue_name, $exchange_name, $routing_key_name){
        try{
            $this->queue = new AMQPQueue($this->channel);
            $this->queue->setName($queue_name);
            if($this->durable){
                $this->queue->setFlags(AMQP_DURABLE);
            }else{
                $this->queue->setFlags(AMQP_AUTODELETE);
            }
            if($this->mirror){
                $this->queue->setArgument('x-ha-policy','all');
            }
            $this->queue->declare();
            $routing_key_names = is_array($routing_key_name) ? $routing_key_name : array($routing_key_name);
            foreach($routing_key_names as $value){
                $this->queue->bind($exchange_name, $value);
            }
        }catch(Exception $e){
            $this->queue = null;
        }
    }
      
    public function __destruct(){
        if($this->conn){
            $this->conn->disconnect();
        }
    }
}
?>