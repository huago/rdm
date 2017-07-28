<?php
class ConnectionModel extends Cola_Model
{
	protected $_table = 'rdm_connection';
	protected $_pk	=	'id';

	public function __construct()
	{
		$this->db = $this->db('_rdmdb');
	}
}