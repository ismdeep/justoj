<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/22
 * Time: 3:09 PM
 */

namespace app\api\controller;


use app\api\model\UserModel;
use app\extra\controller\ApiBaseController;
use app\extra\util\PasswordUtil;

class Changepassword extends ApiBaseController
{
    /**
     * @param $user_id
     * @param $password
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function change_password($user_id, $password)
	{
		// 判断用户是否登录
		intercept_json(null == $this->loginuser, $this->lang['not_login']);
		// 判断用户是否存在
		$user = UserModel::get(['user_id' => $user_id]);
		intercept_json(!$user, $this->lang['user_not_exists']);
		// 判断权限
        intercept_json(!($this->is_administrator || $user->user_id == $this->loginuser->user_id), $this->lang['do_not_have_privilege']);
		// 生成新的密码
		$user->password = PasswordUtil::gen_password($password);
		$user->save();
		return json(['status' => 'success', 'msg' => $this->lang['password_has_been_changed']]);
	}
}
