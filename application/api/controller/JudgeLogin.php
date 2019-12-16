<?php


namespace app\api\controller;


use app\api\model\PrivilegeModel;
use app\api\model\UserModel;
use app\extra\controller\ApiBaseController;
use app\extra\util\PasswordUtil;
use think\Session;

class JudgeLogin extends ApiBaseController
{
    /**
     * 判断判题机是否已经登陆
     *
     * http://justoj-web.ismdeep.com/api/judge_login/check_login
     */
    public function check_login()
    {
        if ($this->is_administrator) {
            echo "1";
        } else {
            echo "0";
        }
    }


    /**
     * 判题机登陆接口
     *
     * http://justoj-web.ismdeep.com/api/judge_login/login?user_id=ismdeep&password=wq1stHack3r
     *
     * @param string $user_id
     * @param string $password
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($user_id = '', $password = '')
    {
        intercept('' == $user_id, 'USER ID ERROR');
        intercept('' == $password, 'PASSWORD ERROR');
        $user = (new UserModel())->where('user_id', $user_id)->find();
        intercept(null == $user, 'USER NOT EXISTS');

        if (PasswordUtil::check_password($password, $user->password)) {
            Session::set('user', $user);

            // 判断是否是管理员administrator
            if (PrivilegeModel::get(['user_id' => $user_id, 'rightstr' => 'administrator'])) {
                Session::set('administrator', $user);
            }

            // 判断是否是root账号
            if (PrivilegeModel::get(['user_id' => $user_id, 'rightstr' => 'root'])) {
                Session::set('root', $user);
            }

            return json(['status' => 'success', 'msg' => $this->lang['login_success']]);
        }else{
            return json(['status' => 'error', 'msg' => $this->lang['wrong_password']]);
        }
    }
}