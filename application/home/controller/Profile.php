<?php


namespace app\home\controller;


use app\api\model\EmailCodeModel;
use app\api\model\UserModel;
use app\home\common\HomeBaseController;
use app\extra\util\PasswordUtil;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Env;
use think\exception\DbException;
use think\Request;
use think\response\Json;

class Profile extends HomeBaseController {

    public function index() {
        if (!$this->login_user) {
            $this->redirect('/login?redirect=%2Fprofile');
        }
        $user = UserModel::get(['user_id' => $this->login_user->user_id]);
        $user->school = htmlspecialchars($user->school);
        $user->academy = htmlspecialchars($user->academy);
        $user->class = htmlspecialchars($user->class);
        $user->phone = htmlspecialchars($user->phone);
        $user->email = htmlspecialchars($user->email);
        $this->assign('user', $user);
        return view($this->theme_root . '/profile');
    }

    public function verify_email() {
        if (!$this->login_user) {
            $this->redirect('/');
        }

        return view($this->theme_root . '/profile_verify_email', [
            'user' => $this->login_user
        ]);
    }

    public function send_verify_code_json() {
        if (!$this->login_user) {
            return json(['code' => 500, 'msg' => 'not login']);
        }

        if (!$this->login_user->email) {
            return json(['code' => 404, 'msg' => '请设置邮箱账号']);
        }

        $email_verify_code = PasswordUtil::random_string('0123456789', 6);

        $email_code = new EmailCodeModel();
        $email_code->email = $this->login_user->email;
        $email_code->code = $email_verify_code;
        $email_code->save();

        $email_title = "[{$this->site_name}] 绑定邮箱验证码";
        $email_content    = "已收到你的绑定邮箱要求，请输入验证码：{$email_verify_code}，该验证码1440分钟内有效。

感谢对{$this->site_name}的支持，再次希望你在{$this->site_name}的体验有益和愉快。

-- {$this->site_name}

(这是一封自动产生的email，请勿回复。)";

        $send_result = send_email(
            $this->login_user->email, $this->login_user->user_id, $email_title,
            $email_content, false
        );

        if (!$send_result) {
            return json(['code' => 500, 'msg' => '发送失败']);
        }

        return json(['code' => 0, 'msg' => '发送成功']);
    }

    /**
     * @param string $code
     *
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function verify_email_code_json($code = '') {
        if (!$this->login_user) {
            return json(['code' => 500, 'msg' => 'not login']);
        }

        if (!$this->login_user->email) {
            return json(['code' => 404, 'msg' => '请设置邮箱账号']);
        }

        // @TODO 设定搜索时间
        $email_code = (new EmailCodeModel())
            ->where(['email' => $this->login_user->email, 'code' => $code])->find();

        if (!$email_code) {
            return json(['code' => 400, 'msg' => '验证失败']);
        }

        $user = UserModel::get(['user_id' => $this->login_user->user_id]);
        $user->email_verified = 1;
        $user->save();

        return json(['code' => 0, 'msg' => 'success']);
    }

    public function change_email() {
        if (!$this->login_user) {
            $this->redirect('/');
        }

        return view($this->theme_root . '/profile_change_email');
    }

    public function change_email_json($email = '') {
        if (!$this->login_user) {
            return json(['code' => 500, 'msg' => 'not login']);
        }

        // 判断当前邮箱是否已经被占用
        $user_with_email = (new UserModel())->where(['email' => $email, 'email_verified' => 1])->find();
        if ($user_with_email) {
            return json(['code' => 500, 'msg' => '已经被占用']);
        }

        $user = UserModel::get(['user_id' => $this->login_user->user_id]);
        $user->email = $email;
        $user->email_verified = 0;
        $user->save();

        return json(['code' => 0, 'msg' => 'success']);
    }

    public function change_password() {
        if (!$this->login_user) {
            $this->redirect('/');
        }
        return view($this->theme_root . '/change-password');
    }

    public function edit_my_profile() {
        if (null == $this->login_user) {
            $this->redirect('/');
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