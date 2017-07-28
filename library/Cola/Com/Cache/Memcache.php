<?php

class Cola_Com_Cache_Memcache extends Cola_Com_Cache_Abstract
{
    protected $_connection;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        $this->_connection = new Memcache();

        parent::__construct($options);

        foreach ($this->_options['servers'] as $server) {
            $server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => true);
            $this->_connection->addServer($server['host'], $server['port'], $server['persistent']);
        }
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function set($id, $data, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->_options['ttl'];
        }
        //添加return，by liuweixing 2012-06-15
        return $this->_connection->set($id, $data, empty($this->_options['compressed']) ? 0 : MEMCACHE_COMPRESSED, $ttl);
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
	public function get($id)
	{
		return $this->_connection->get($id);
	}

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
		//添加return，by liuweixing 2012-06-15
        return $this->_connection->delete($key);
    }

    /**
     * 新增crementEx方法(去掉了crement方法)
     * 支持如下两个变化
     * 1. 如果key不存在，自动重新创建
     * 2. 支持负数和正数，自动根据符号判断
     * 3. 注意数据的最终结果不支持负数。最小为0
     * value可为正或负
     * @param string $key
     * @param int $value
     * by liuweixing 2012-06-15
     */
       public function crementEx($key, $value = 1)
       {
        if($value == 0) return true;//为0直接返回

        //如果key不存在，直接设置
        if (false === ($tmp = $this->get($key))) {
            return $this->set($key, $value);
        }
        //如果结果为负数，防止出错，这里set成0
        if($tmp<0){
            return $this->set($key, 0);
        }
        
        //判断value的正负分别处理
        if ($value > 0 ) {
            return $this->_connection->increment($key, $value);//递加
        } else {
            return $this->_connection->decrement($key, -$value);//递减
        }
      }

    /**
     * Increment value
     *
     * @param string $key
     * @param int $value
     */
    public function increment($key, $value = 1)
    {
        return $this->_connection->increment($key, $value);
    }

    /**
     * clear cache
     */
    public function clear()
    {
        $this->_connection->flush();
    }

    protected function close()
    {
        $this->_connection->close();
    }

	public function stats()
	{
		return $this->_connection->getStats();
	}
}
