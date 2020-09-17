<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 10:44 PM
 */

namespace app\api\controller;


use app\api\model\UiLanuageModel;
use app\extra\controller\ApiBaseController;
use think\Session;

class Uilanguage extends ApiBaseController {
    public function set_lang($language = '') {
        if ('' == $language) return json(['status' => 'error', 'msg' => 'Arguments error.']);
        if ('cn' == $language || 'en' == $language) {
            // 判断用户是否登录
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
            } else {
                Session::set('ui_language', $language);
            }
            return json(['status' => 'success']);
        }
        return json(['status' => 'error', 'msg' => 'Arguments error.']);
    }
}