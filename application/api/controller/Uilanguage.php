<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/7
 * Time: 10:44 PM
 */

namespace app\api\controller;


use app\api\model\UiLanuageModel;
use app\api\common\ApiBaseController;
use app\api\model\UserModel;
use think\Session;

class Uilanguage extends ApiBaseController {
    public function set_lang($language = '') {
        if ('' == $language) return json(['status' => 'error', 'msg' => 'Arguments error.']);
        if ('cn' == $language || 'en' == $language) {
            /* 判断用户是否登录 */
            if ($this->login_user) {
                $uilanguage = UiLanuageModel::get(['user_id' => $this->login_user->user_id]);
                if ($uilanguage) {
                    $uilanguage->language = $language;
                    $uilanguage->save();
                } else {
                    $uilanguage = new UiLanuageModel();
                    $uilanguage->user_id = $this->login_user->user_id;
                    $uilanguage->language = $language;
                    $uilanguage->save();
                }
                /* @var $user UserModel */
                $user = (new UserModel())->where('user_id', $this->login_user->user_id)->find();
                $user->fk_session_info();
                Session::set('user', $user);
            } else {
                Session::set('ui_language', $language);
            }
            return json(['status' => 'success']);
        }
        return json(['status' => 'error', 'msg' => 'Arguments error.']);
    }
}