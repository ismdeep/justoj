<?php


namespace app\home\controller;

use app\api\model\PasswordResetLinkModel;
use app\api\model\UserModel;
use app\common\controller\BaseController;
use app\extra\util\PasswordUtil;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\response\Json;

class ForgotPassword extends BaseController {

    public function index() {
        return view($this->theme_root . '/forgot-password');
    }

    /**
     * @param string $account
     * @return Json
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function send_email_json($account = '') {
        if (!$account) {
            return json(['code' => 500, 'msg' => 'Please input account[username/email].']);
        }

        /* @var $user UserModel */
        $user = (new UserModel())->whereOr(['user_id' => $account, 'email' => $account])->find();

        if ($user->email_verified != 1) {
            return json(['code' => 500, 'msg' => 'Can NOT find the user or the user did NOT verified the email.']);
        }

        $password_reset_link = new PasswordResetLinkModel();
        $password_reset_link->user_id = $user->user_id;
        $password_reset_link->uuid = PasswordUtil::random_string('0123456789abcdef', 32);
        $password_reset_link->save();

        $reset_password_url = request()->domain() . "/forgot-password/reset-password/{$password_reset_link->uuid}";

        $email_title = "[{$this->site_name}] 找回密码";
        $email_content = "<p>已收到你的找回密码要求，请访问以下链接进行设置密码，该链接1440分钟内有效。</p>        
        
        <p>点击<a href='{$reset_password_url}'>此链接</a>进行设置密码。</p>
        
        或访问此链接：{$reset_password_url}
        

<p>感谢对{$this->site_name}的支持，再次希望你在{$this->site_name}的体验有益和愉快。</p>

<p>-- {$this->site_name}</p>

<p>(这是一封自动产生的email，请勿回复。)</p>";

        $send_result = send_email($user->email, $user->user_id, $email_title, $email_content, true);


        $msg_arr = [
            'en' => "Email has been send to: {$user->email}, please check out.",
            'cn' => "邮件已发送至：{$user->email} ，请查收。"
        ];

        $msg = $msg_arr[$this->show_ui_lang];

        return json([
            'code' => 0,
            'msg' => $msg,
            'data' => $user,
            'send_result' => $send_result,
            'content' => $email_content
        ]);
    }

    /**
     * @param string $uuid
     * @return mixed|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function reset_password($uuid = '') {
        intercept(!$uuid, 'Error Page');

        /* @var $password_reset_link PasswordResetLinkModel */
        $password_reset_link = (new PasswordResetLinkModel())->where('uuid', $uuid)->find();
        if (!$password_reset_link) {
            return 'Error Page';
        }

        $user = (new UserModel())->where('user_id', $password_reset_link->user_id)->find();

        return view($this->theme_root . '/reset-password', [
            'user' => $user,
            'password_reset_link' => $password_reset_link
        ]);
    }

    /**
     *
     * @param string $uuid
     * @param string $password
     * @param string $password_again
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function set_password_json($uuid = '', $password = '', $password_again = '') {
        if (!$uuid) {
            return json(['code' => 500, 'msg' => 'Error on UUID']);
        }

        /* @var $password_reset_link PasswordResetLinkModel */
        $password_reset_link = (new PasswordResetLinkModel())->where('uuid', $uuid)->find();
        if (!$password_reset_link) {
            return json(['code' => 500, 'msg' => 'Error on UUID']);
        }

        if (!$password || $password != $password_again) {
            return json(['code' => 500, 'msg' => 'Password can NOT be empty and the password you type two times should be same.']);
        }

        /* @var $user UserModel */
        $user = (new UserModel())->where('user_id', $password_reset_link->user_id)->find();
        $user->password = PasswordUtil::gen_password($password);
        $user->save();

        return json(['code' => 0, 'msg' => 'Success']);
    }

}