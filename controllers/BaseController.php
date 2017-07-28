<?php
class BaseController extends Cola_Controller
{
	public function __construct()
	{
		parent::__construct();

		$cola = Cola::getInstance();
		$dispatchInfo = $cola->getDispatchInfo();
		$controller = strtolower($dispatchInfo['controller']);
		$action = strtolower($dispatchInfo['action']);

		$requestUri = $controller . '/' . $action;

		$notCheckUri = array('indexcontroller/loginaction', 'indexcontroller/regaction');

		if (!in_array($requestUri, $notCheckUri)) {
			$this->checkLogin();
		}

		if (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {
			//查询数据库，获取用户的连接列表
			$condition = 'uid=' . $_SESSION['uid'];
			$allmenu = $this->model('connection')->find($condition);

			$this->view->allmenu = $allmenu;
		}
	}

	private function checkLogin()
	{
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

		if (empty($uid)) {
			//redirect to login page
			$this->redirect('/index/login');
		}
	}
}