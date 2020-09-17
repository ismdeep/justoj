<?php


namespace app\home\controller;


use app\api\model\UserModel;
use app\extra\controller\UserBaseController;

class Profile extends UserBaseController {

    public function index() {
        if (!$this->login_user) {
            $this->redirect('/login?redirect=%2Fprofile');
        }
        $user = UserModel::get(['user_id' => $this->login_user->user_id]);
        $user->school = htmlspecialchars($user->school);
        $user->academy = htmlspecialchars($user->academy);
        $user->class = htmlspecialchars($user->class);
        $user->phone = htmlspecialchars($user->phone);
        $this->assign('user', $user);
        return view($this->theme_root . '/profile');
    }

    public function change_password() {
        if (!$this->login_user) {
            $this->redirect('/');
        }
        return view($this->theme_root . '/change-password');
    }

    public function edit_my_profile() {
        if (null == $this->login_user) {
            return $this->redirect('/');
        }

        $user = UserModel::get(['user_id' => $this->login_user->user_id]);
        $user->school = htmlspecialchars($user->school);
        $user->academy = htmlspecialchars($user->academy);
        $user->class = htmlspecialchars($user->class);
        $user->phone = htmlspecialchars($user->phone);
        $this->assign('user', $user);
        return view($this->theme_root . '/edit-my-profile');
    }

}