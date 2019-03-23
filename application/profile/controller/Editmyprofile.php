<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/6/7
 * Time: 10:42
 */

namespace app\profile\controller;


use app\api\model\UserModel;
use app\extra\controller\UserBaseController;

class Editmyprofile extends UserBaseController
{
	public function index()
	{
	    if (null == $this->loginuser) {
	        return $this->redirect('/');
        }
	    $user = UserModel::get(['user_id' => $this->loginuser->user_id]);
	    $user->school = htmlspecialchars($user->school);
        $user->academy = htmlspecialchars($user->academy);
        $user->class = htmlspecialchars($user->class);
        $user->phone = htmlspecialchars($user->phone);
	    $this->assign('user', $user);
		return view();
	}
}