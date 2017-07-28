<?php
class IndexController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$this->display('index.html');
	}

	public function regAction()
	{
		$this->display('reg.html');
	}

	public function loginAction()
	{
		$this->display('login.html');
	}
}