<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 11:46 PM
 */

namespace app\register\controller;


use app\extra\controller\UserBaseController;

class Index extends UserBaseController
{
	public function index()
	{
		return view($this->theme_root . '/register');
	}
}
