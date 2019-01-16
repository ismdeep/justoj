<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/9/28
 * Time: 2:56 PM
 */

namespace app\admin\controller;


use app\api\model\UserModel;
use app\extra\controller\AdminBaseController;
use app\extra\util\PasswordUtil;

class My extends AdminBaseController
{
    public function change_password()
    {
        return view();
    }

    /**
     * 管理员修改自己密码 json接口
     *
     * @param string $newpassword
     * @param string $newpassword2
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function change_password_json($newpassword = '', $newpassword2 = '')
    {
        intercept_json('' == $newpassword || $newpassword != $newpassword2, '新密码不可为空且两次输入的密码必须相同。');

        $user = (new UserModel())->where('user_id', $this->loginuser->user_id)->find();
        $user->password = PasswordUtil::gen_password($newpassword);
        $user->save();

        return json([
            'status' => 'success',
            'msg' => '密码修改成功'
        ]);
    }
}