<?php
class ApiController extends Cola_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function loginAction()
	{
		$username = $_POST['username'];
		$password = $_POST['password'];

		$condition = 'username="' . $username .  '" AND password="' . $password . '"';

		$user = $this->model('user')->find($condition);
		$user = current($user);

		if ($user) {
			$_SESSION['uid'] = $user['id'];
			$_SESSION['username'] = $username;

			$this->output($code = 200, $data = array(), $message = '');
		}

		$this->output($code = 0, $data = array(), $message = '用户不存在');
	}

	public function regAction()
	{
		$username = isset($_POST['username']) ? trim($_POST['username']) : '';
		$password = isset($_POST['password']) ? trim($_POST['password']) : '';

		if (!$username) {
			$this->output($code = 0, $data = array(), $message = '账号不能为空');
		} 

		if (!$password) {
			$this->output($code = 0, $data = array(), $message = '密码不能为空');
		}

		//查询账号是否存在
		$condition = 'username="' . $username . '"';
		$userResult = $this->model('user')->find($condition);

		if ($userResult) {
			$this->output($code = 0, $data = array(), $message = '账号已存在');
		}

		$userId = $this->model('user')->create($username, $password);

		if (!$userId) {
			$this->output($code = 0, $data = array(), $message = '注册失败');
		}

		//自动登录
		$_SESSION['uid'] = $userId;
		$_SESSION['username'] = $username;

		$this->output($code = 200, $data = array(), $message = '');
	}

	public function quitAction()
	{
		$_SESSION = array();
		@session_destroy();

		$this->output($code = 200, $data = array(), $message = '');
	}

	public function deletekeyAction()
	{
		$id = isset($_GET['id']) ? $_GET['id'] : 0;
		$key = isset($_GET['key']) ? $_GET['key'] : 0;
		$db = isset($_GET['db']) ? $_GET['db'] : 0;

		if (!$id || !$key) {
			$this->output($code = 0, $data = array(), $message = '参数有误');
		}

		//查询host，port和auth
		$condition = 'id=' . $id . ' AND uid=' . $_SESSION['uid'];

		$connection = $this->model('connection')->find($condition);

		$connection = current($connection);

		if (!$connection) {
			$this->output($code = 0, $data = array(), $message = '您没有权限查询此主机');
		}

		$redis = $this->model('redis')->getInstance($connection['host'], $connection['port'], $connection['auth']);

		if ($redis) {
			$redis->select($db);
			$isExist = $redis->exists($key);

			if (!$isExist) {
				$this->output($code = 0, $data = array(), $message = 'key不存在');
			}
			
			$deleteResult = $redis->del($key);

			if (!$deleteResult) {
				$this->output($code = 0, $data = array(), $message = '删除key失败');
			}

			$this->output($code = 200, $data = array(), $message = '删除key成功');
		} else {
			$this->output($code = 0, $data = array(), $message = '连接失败');
		}
	}
}