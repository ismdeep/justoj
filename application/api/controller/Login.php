<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/8
 * Time: 10:25
 */

namespace app\api\controller;


use app\api\model\PrivilegeModel;
use app\api\model\UserModel;
use app\extra\controller\ApiBaseController;
use app\extra\util\PasswordUtil;
use think\Session;

class Login extends ApiBaseController
{
	public function islogin()
	{
		if ($this->loginuser) return json(['status' => 'success', 'data' => $this->loginuser]);
		return json(['status' => 'error', 'msg' => 'Is not login.']);
	}

	public function login($username,$password)
	{
		$user = UserModel::get(['user_id' => $username]);
		if (!$user) return json(['status' => 'error', 'msg' => $this->lang['user_not_exists']]);
		if (PasswordUtil::check_password($password, $user->password)) {
			Session::set('user', $user);

			// 判断是否是管理员administrator
			if (PrivilegeModel::get(['user_id' => $username, 'rightstr' => 'administrator'])) {
				Session::set('administrator', $user);
			}

			// 判断是否是root账号
			if (PrivilegeModel::get(['user_id' => $username, 'rightstr' => 'root'])) {
				Session::set('root', $user);
			}

			return json(['status' => 'success', 'msg' => $this->lang['login_success']]);
		}else{
			return json(['status' => 'error', 'msg' => $this->lang['wrong_password']]);
		}
	}
}
