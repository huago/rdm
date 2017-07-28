<?php
class KeyController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$id = $_GET['id'];
		$key = $_GET['key'];

		if (!$id || !$key) {
			$this->redirect('/');
		}

		//查询host，port和auth
		$condition = 'id=' . $id . ' AND uid=' . $_SESSION['uid'];

		$connection = $this->model('connection')->find($condition);

		$connection = current($connection);

		if (!$connection) {
			$this->redirect('/');
		}

		$redis = $this->model('redis')->getInstance($connection['host'], $connection['port'], $connection['auth']);

		if ($redis) {
			$db = isset($_GET['db']) ? intval($_GET['db']) : 0;
			$redis->select($db);
			//查询key是否存在
			$isExist = $redis->exists($key);

			if (!$isExist) {
				$this->redirect('/db/index?id=' . $id . '&db=' . $db);
			}

			$keys = $redis->keys('*');
			$type = $redis->type($key);
			$ttl = $redis->ttl($key);

			$typeMap = array(
				0	=>	'NONE',
				1	=>	'STRING',
				2	=>	'SET',
				3	=>	'LIST',
				4	=>	'ZSET',
				5	=>	'HASH',
				);

			$this->view->key_type = $typeMap[$type];
			$this->view->key_type_num = $type;

			switch ($type) {
				case 1:
					$value = $redis->get($key);
					break;
				case 2:
					$value = $redis->smembers($key);
					break;
				case 3:
					$value = $redis->lrange($key, 0, -1);
					break;
				case 4:
					$value = $redis->zrange($key, 0, -1, true);
					break;
				case 5:
					$value = $redis->hgetall($key);
					break;
				default:
					$value = '';
					break;
			}

			$this->view->active_id = $id;
			$this->view->active_db = $db;
			$this->view->keys = $keys;
			$this->view->key = $key;
			$this->view->ttl = $ttl;
			$this->view->value = $value;
			$this->view->iskey = 1;
			$this->display('index.html');
		} else {
			exit('Connect redis error...');
		}
	}
}