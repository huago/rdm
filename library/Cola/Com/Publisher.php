<?php
class Cola_Com_Publisher{  
    private $config = array();
    private $durable = false;
    private $conn = null;
    private $channel = null;
    private $exchange = null;
    private $exchange_types = array('direct'=>AMQP_EX_TYPE_DIRECT,'fanout'=>AMQP_EX_TYPE_FANOUT,'topic'=>AMQP_EX_TYPE_TOPIC);
     
    /** 
    * 创建连接、交换机
    * @param array  $config RabbitMQ服务器信息 array('host'=>'127.0.0.1' , 'port'=> '5672', 'login'=>'guest' , 'password'=> 'guest','vhost' =>'/');
    * @param string $exchange_name 交换机名称 
    * @param string $exchange_type 交换机类型 direct fanout topic
    * @param bool   $durable 消息是否持久化
    * @return void  
    */
    public function __construct($config, $exchange_name, $exchange_type = 'direct', $durable = false){
        if(!is_array($config) || empty($config) || empty($exchange_name) || empty($this->exchange_types[$exchange_type])){
            return false;
        }
        $this->config = $config;
        if(!self::connect()){
            return false;
        }
        $this->channel = new AMQPChannel($this->conn);
        $this->durable = (bool)$durable;
        $this->declareExchange($exchange_name, $this->exchange_types[$exchange_type]);
    }
     
    /** 
    * 创建连接
    * @return bool
    */
    private function connect(){
        try{
            $this->conn = new AMQPConnection($this->config);
            return $this->conn -> connect();
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
    * 消息发布
    * @param mix    $message 消息,若是数组转换为json格式 
    * @param string $routing_key_name 路邮键
    * @return bool  
    */
    public function send($message, $routing_key_name){  
        if(empty($message) || empty($routing_key_name) || empty($this->exchange)){
            return false;
        }
        if(is_array($message)){
            $message = json_encode($message);
        }else{
            $message = strval($message);
        }
        if($this->durable){
            return $this->exchange -> publish($message, $routing_key_name, AMQP_NOPARAM, array('delivery_mode'=>2));
        }else{
            return $this->exchange -> publish($message, $routing_key_name);
        }
    }
     
    public function __destruct(){
        if($this->conn){
            $this->conn->disconnect();
        }
    }
}
?>