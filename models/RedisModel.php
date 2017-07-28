<?php
class RedisModel
{
	private static $redis = null;

	public static function getInstance($host, $port = 6379, $auth = null) 
	{
		if (is_null(self::$redis) || empty(self::$redis)) {
			if (empty($host)) {
				return false;
			}
			try{
				self::$redis = new Redis();
				@self::$redis->connect($host, $port, 3);
				if (!is_null($auth)) {
					self::$redis->auth($auth);
				}
			}catch(Exception $e){
				exit('Connect Timeout');
			}
		}

		return self::$redis;
	}
}