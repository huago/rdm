<?php
class Common_Rabbitmq {
	protected $_config = array('host'=>'' , 'port'=>'', 'login'=>'', 'password'=>'' , 'vhost' =>'/');
	protected $_connection;
	protected $_channel;
	protected static $_exchange = array();
	protected static $_queue = array();
	protected static $_declareQueue = array();

	public function __construct($config = array())
	{
		$this-> _connection = new AMQPConnection(array_merge($this-> _config,$config));
		$this-> _connection->connect();
		$this-> _channel = new AMQPChannel($this->_connection);
	}

	/**
	 *
	 * 声明个交换机
	 * @param string $ex_name    交换机名称
	 * @param string $ex_type    交换机类型
	 * @param string $ex_flags   交换机标志
	 * @return object
	 */
	private function exchange($ex_name,$ex_type = AMQP_EX_TYPE_TOPIC,$ex_flags = AMQP_DURABLE)
	{
		if(!isset (self:: $_exchange[$ex_name]) || is_null(self::$_exchange [$ex_name])){
			$exchange = new AMQPExchange($this->_channel );
			$exchange->setName($ex_name);
			$exchange->setType($ex_type);
			$exchange->setFlags($ex_flags);
			$exchange->declare();
			self::$_exchange [$ex_name] = $exchange;
			return $exchange;
		} else{
			return self::$_exchange [$ex_name];
		}
	}

	/**
	 *
	 * 声明个队列并绑定
	 * @param string $qu_name    队列名称
	 * @param string $ex_name    交换机名称
	 * @param string $rk_name    路由键名称
	 * @param string $qu_flags   路由标志
	 * @return object
	 */
	private function queue($qu_name, $ex_name, $rk_name, $qu_flags = AMQP_DURABLE)
	{
		if(!isset (self:: $_queue[$qu_name][$ex_name][$rk_name]) || is_null(self::$_queue [$qu_name][$ex_name][$rk_name])){
			$queue = new AMQPQueue($this->_channel );
			$queue->setName($qu_name);
			$queue->setFlags($qu_flags);
			$queue->declare();
			$queue->bind($ex_name, $rk_name);
			self::$_queue [$qu_name][$ex_name][$rk_name] = $queue;
			return $queue;
		} else{
			return self::$_queue [$qu_name][$ex_name][$rk_name];
		}
	}


	/**
	 *
	 * 声明个队列
	 * @param string $qu_name    队列名称
	 * @param string $qu_flags   路由标志
	 * @return object
	 */
	private function declareQueue($qu_name, $qu_flags = AMQP_DURABLE)
	{
		if(!isset (self:: $_declareQueue[$qu_name]) || is_null(self::$_declareQueue [$qu_name])){
			$queue = new AMQPQueue($this->_channel );
			$queue->setName($qu_name);
			$queue->setFlags($qu_flags);
			$queue->declare();
			self::$_declareQueue [$qu_name] = $queue;
			return $queue;
		} else{
			return self::$_declareQueue [$qu_name];
		}
	}

	/**
	 *
	 * 向队列发消息
	 * @param string $ex_name    交换机名称
	 * @param string $rk_name    路由键名称
	 * @param string $qu_name    队列名称
	 * @param string $message    发送的消息
	 * @return boolean
	 */
	public function set($ex_name, $rk_name, $qu_name, $message)
	{
		if(is_array($message)){$message = json_encode($message);}
		$exchange = $this->exchange($ex_name);
		$this->queue($qu_name,$ex_name,$rk_name);
		$this-> _channel->startTransaction();
		$resoult = $exchange ->publish($message, $rk_name, AMQP_NOPARAM, array('delivery_mode'=>2));
		$this-> _channel->commitTransaction();
		return $resoult;
	}

	/**
	 *
	 * 从队列取消息
	 * @param string $ex_name    交换机名称
	 * @param string $rk_name    路由键名称
	 * @param string $qu_name    队列名称
	 * @param string $flags      取消息标志
	 * @return object
	 */
	public function get($qu_name, $count = 1, $flags = AMQP_AUTOACK)
	{
		$resoult = array();
		for ($i=0;$i<$count;$i++){
			try {
				$message = $this->declareQueue($qu_name)->get($flags);
			} catch (Exception $e) {
				$message = false;
			}
			if($message){
				$resoult[] =  $message->getBody();
			}
		}
		if(1 == $count) {
			return !empty($resoult[0])?$resoult[0]:"";
		}else{
			return $resoult;
		}
	}

	public function __destruct()
	{
		return $this->_connection ->disconnect();
	}
}
?>
