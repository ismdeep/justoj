<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 22:10
 */

namespace app\login\controller;


use app\api\model\PrivilegeModel;
use app\api\model\UserModel;
use app\extra\controller\UserBaseController;
use app\extra\util\PasswordUtil;
use think\Session;

class Post extends UserBaseController {
    /**
     *
     * @param $username
     * @param $password
     * @param string $redirect
     * @throws \think\exception\DbException
     */
    public function index($username, $password, $redirect = '/') {
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
