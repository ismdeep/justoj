<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
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
		/* 判断用户是否登录 */
		intercept_json(null == $this->login_user, $this->lang['not_login']);

		/* 判断用户是否存在 */
		$user = UserModel::get(['user_id' => $user_id]);
		intercept_json(!$user, $this->lang['user_not_exists']);

		/* 判断权限 */
        $passed = false;
        if ($this->login_user && $this->login_user->is_admin) {
            $passed = true;
        }
        if ($this->login_user && $this->login_user->user_id == $user->user_id) {
            $passed = true;
        }
        intercept_json(!$passed, $this->lang['do_not_have_privilege']);

		/* 生成新的密码 */
		$user->password = PasswordUtil::gen_password($password);
		$user->save();

		return json(['status' => 'success', 'msg' => $this->lang['password_has_been_changed']]);
	}
}
