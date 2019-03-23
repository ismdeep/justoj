<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/6/4
 * Time: 11:01
 */

namespace app\profile\controller;


use app\extra\controller\UserBaseController;
use think\Request;

class Changepassword extends UserBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		if (!$this->loginuser) {
			$this->redirect('/');
		}
	}

	public function index()
	{
		return view($this->theme_root . '/change-password');
	}
}