<?php /** @noinspection PhpUndefinedFieldInspection */

/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 12:01 AM
 */

namespace app\api\controller;


use app\api\model\UserModel;
use app\extra\controller\ApiBaseController;
use app\extra\util\PasswordUtil;
use think\Exception;
use think\exception\DbException;

class User extends ApiBaseController {
    /**
     * register a new user account
     * @param string $username
     * @param string $password
     * @param string $password_again
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function register($username = '', $password = '', $password_again = '') {
        if (!$username) return json(['status' => 'error', 'msg' => $this->show_ui_lang == 'en' ? 'Username can NOT be empty' : '用户名不可为空']);
        if (strlen($username) < 4) return json(['status' => 'error', 'msg' => $this->show_ui_lang == 'en' ? 'Username length must >= 4' : '用户名长度必须大于或等于4']);
        if (!preg_match("/^[A-Za-z0-9_]+$/", $username)) {
            return json(['status' => 'error', 'msg' => $this->show_ui_lang == 'en' ? 'Username is invalid, only letters(a-z) , digits(0-9) and underscore(_) are allowed.' : '用户名不符合规范，仅支持英文字母和数字以及下划线']);
        }

        if (!$password) return json(['status' => 'error', 'msg' => $this->lang['password_cant_be_empty']]);
        if ($password != $password_again) return json(['status' => 'error', 'msg' => $this->lang['password_and_password_again_must_be_same']]);

        // 匹配一卡通号并限制必须为完整的一卡通号。如：1520113526
        if (preg_match('/^[0-9]*$/', $username) && strlen($username) < 10) {
            return json(['status' => 'error', 'msg' => '请填写完整的一卡通号。']);
        }

        // 判断用户是否存在
        if (UserModel::get(['user_id' => $username])) return json(['status' => 'error', 'msg' => '用户已存在']);
        // 生成密码
        $password = PasswordUtil::gen_password($password);

        // 写入数据
        $user = new UserModel();
        $user->user_id = $username;
        $user->defunct = 'N';
        $user->realname = $username;
        $user->password = $password;
        $user->reg_time = date('Y-m-d H:i:s', time());
        $user->save();
        return json(['status' => 'success', 'msg' => '注册成功']);
    }

    /**
     * user change password
     * @param $password
     * @param $password_again
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function change_password($password = '', $password_again = '') {
        if (!$this->login_user) {
            return json(['status' => 'error', 'msg' => $this->lang['not_login']]);
        }

        if ('' == $password) {
            return json(['status' => 'error', 'msg' => $this->lang['password_cant_be_empty']]);
        }

        if ($password != $password_again) {
            return json(['status' => 'error', 'msg' => $this->lang['password_and_password_again_must_be_same']]);
        }

        $user = UserModel::get(['user_id' => $this->login_user->user_id]);
        $user->password = PasswordUtil::gen_password($password);
        $user->save();
        return json(['status' => 'success', 'msg' => $this->lang['password_has_been_changed']]);
    }

    /**
     * 修改个人信息
     *
     * @param string $nickname
     * @param string $email
     * @param string $phone
     * @param string $school
     * @param string $academy
     * @param string $class
     * @param string $realname
     * @return \think\response\Json
     * @throws DbException
     */
    public function modify_profile($nickname = '', $email = '', $phone = '', $school = '', $academy = '', $class = '', $realname = '') {
        intercept_json('' == $nickname, 'nickname can not be empty.');
        intercept_json('' == $school, 'school can not be empty.');
        intercept_json('' == $academy, 'academy can not be empty.');
        intercept_json('' == $class, 'class can not be empty.');
        intercept_json('' == $realname, 'realname can not be empty.');
        if (!$this->login_user) {
            return json([
                'status' => 'error',
                'msg' => $this->lang['not_login']
            ]);
        }

        // 验证邮箱合法性
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json([
                'status' => 'error',
                'msg' => 'email is invalid'
            ]);
        }

        $user = UserModel::get(['user_id' => $this->login_user->user_id]);
        $user->email = $email;
        $user->nick = htmlspecialchars($nickname);
        $user->realname = htmlspecialchars($realname);
        $user->phone = $phone;
        $user->school = $school;
        $user->academy = $academy;
        $user->class = $class;

        try {
            $user->save();
        } catch (DbException $e) {
            return json([
                'status' => 'error',
                'msg' => $e->getMessage()
            ]);
        }

        return json([
            'status' => 'success',
            'msg' => 'ok'
        ]);
    }
}
