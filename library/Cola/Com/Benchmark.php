<?php
class Cola_Com_Benchmark
{
    protected $_time = array();

    public function __construct()
    {
        $this->start();
    }

    public function start($mark = 'start')
    {
        $this->_time = array();
        return $this->mark($mark);
    }

    public function end($mark = 'end')
    {
        return $this->mark($mark);
    }

    public function mark($name = null)
    {
        if (is_null($name)) {
            return $this->_time[] = microtime(true);
        } else {
            return $this->_time[$name] = microtime(true);
        }
    }

    public function cost($p1 = 'start', $p2 = 'end', $decimals = 4)
    {
        $t1 = (empty($this->_time[$p1])) ? $this->mark($p1) : $this->_time[$p1];
        $t2 = (empty($this->_time[$p2])) ? $this->mark($p2) : $this->_time[$p2];

        return abs(number_format($t2 - $t1, $decimals));
    }

    public function step($decimals = 4)
    {
        $t1 = end($this->_time);
        $t2 = $this->mark();
        return number_format($t2 - $t1, $decimals);
    }

    public function time()
    {
        return $this->_time;
    }

    /**
     * Get the amount of memory allocated to PHP
     *
     * Set $flag to TRUE to get the real size of memory allocated from system.
     * If not set or FALSE only the memory used by emalloc() is reported.
     *
     * @param boolean $flag
     * @return int
     */
    public function memory($flag = false)
    {
        return memory_get_usage($flag);
    }
    
	/**
	 * 得到页面最后的错误信息
	 * @author gaojun
	 */
	public static function lastPageError(){
		$lastError = error_get_last();
		if(is_array($lastError)){
			echo '<strong>Page Error</strong>';
			echo '<br />';
			echo 'File:' . $lastError['file'];
			echo '<br />';
			echo 'Line:' . $lastError['line'];
			echo '<br />';
			echo 'Message:' . $lastError['message'];
		}
	}    
    
	/**
	 * 输出当前所有加载的类
	 * @author gaojun
	 */
	public function showClass(){
		global $globalClass;

		echo '<strong>Include Class:</strong>';
		echo '<br />';
		$class = get_declared_classes();
		is_array($globalClass) && $class = array_diff_assoc($class, $globalClass);
		foreach($class as $c){
			echo $c;
			echo "<br />";
		}
	}    
    
    
	/**
	 * 输出当前所有加载的文件
	 * @author gaojun  add 2012-03-22
	 */
	public function showIncludeFile(){
		echo '<strong>Include File:</strong>';
		echo '<br />';
		$file = get_included_files();
		foreach($file as $f)
		{
			echo $f;
			echo "<br />";
		}
	}
    
}