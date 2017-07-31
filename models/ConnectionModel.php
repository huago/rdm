<?php
class ConnectionModel extends Cola_Model
{
	protected $_table = 'rdm_connection';
	protected $_pk	=	'id';

	public function __construct()
	{
		$this->db = $this->db('_rdmdb');
	}

	public function add($title, $host, $port, $auth = '')
	{
		$data = array(
			'title'	=>	trim($title),
			'host'	=>	trim($host),
			'port'	=>	trim($port),
			'auth'	=>	trim($auth),
			'uid'	=>	$_SESSION['uid'],
			);

		$connectionId = $this->insert($data);

		return $connectionId;
	}
}