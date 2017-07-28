<?php
class DbController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$id = $_GET['id'];

		if (!$id) {
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

			$keys = $redis->keys('*');

			$this->view->active_id = $id;
			$this->view->active_db = $db;
			$this->view->keys = $keys;
			$this->display('index.html');
		} else {
			exit('Connect redis error...');
		}
	}
}