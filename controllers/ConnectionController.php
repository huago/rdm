<?php
class ConnectionController extends BaseController
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

		$this->view->active_id = $id;
		$this->display('index.html');
	}
}