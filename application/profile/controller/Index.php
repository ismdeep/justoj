<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/18
 * Time: 17:10
 */

namespace app\profile\controller;


use app\api\model\UserModel;
use app\extra\controller\UserBaseController;

class Index extends UserBaseController
{
	public function index()
	{
		if (!$this->loginuser) {
			$this->redirect('/login?redirect=%2Fprofile');
		}
        $user = UserModel::get(['user_id' => $this->loginuser->user_id]);
        $user->school = htmlspecialchars($user->school);
		$user->academy = htmlspecialchars($user->academy);
		$user->class = htmlspecialchars($user->class);
        $user->phone = htmlspecialchars($user->phone);
        $this->assign('user', $user);
		return view($this->theme_root . '/profile');
	}
}