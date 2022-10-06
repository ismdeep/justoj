<?php


namespace app\home\controller;


use app\api\model\PrivilegeModel;
use app\api\model\UserModel;
use app\extra\util\PasswordUtil;
use app\home\common\HomeBaseController;
use think\Session;

class Auth extends HomeBaseController {

    public function sign_in($redirect = '/') {
        $this->assign('redirect', $redirect);
        return view($this->theme_root . '/login');
    }

    public function sign_out($redirect = '/') {
        Session::clear();
        $this->redirect($redirect);
    }

    public function sign_up() {
        return view($this->theme_root . '/register');
    }

    /**
     *
     * @param $username
     * @param $password
     * @param string $redirect
     * @throws \think\exception\DbException
     */
    public function sign_in_post($username, $password, $redirect = '/') {
        $user = UserModel::get(['user_id' => $username]);
        if (!$user) $this->redirect('/login');
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

            if ('/register' == $redirect) $this->redirect('/');
            $this->redirect($redirect);
        }
        return $this->redirect('/login');
    }

}