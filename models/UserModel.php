<?php
class UserModel extends Cola_Model
{
	protected $_table = 'rdm_user';
	protected $_pk	=	'id';

	public function __construct()
	{
		$this->db = $this->db('_rdmdb');
	}

	public function create($username, $password)
	{
		$sql = "INSERT INTO rdm_user(username, password, salt) VALUES ('{$username}','{$password}','')";
		
		$result = $this->sql($sql);

		return $result;
	}
}