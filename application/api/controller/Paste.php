<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/9/20
 * Time: 10:16 PM
 */

namespace app\api\controller;


use app\api\model\PasteModel;
use app\extra\controller\ApiBaseController;

class Paste extends ApiBaseController {

    /**
     * 贴代码 post接口
     * @param string $lang
     * @param string $code
     * @return \think\response\Json
     */
    public function paste_post($lang = '', $code = '') {
        intercept_json(null == $this->login_user, '尚未登录');
        intercept_json('' == $lang, 'lang参数错误');
        intercept_json('' == $code, '代码不可为空');
        $flag = false;
        $langs = paste_allowed_langs();
        foreach ($langs as $key => $o) {
            if ($lang == $key) {
                $flag = true;
            }
        }
        intercept_json(!$flag, 'lang不合法');

        $paste = new PasteModel();
        $paste->user_id = $this->login_user->user_id;
        $paste->lang = $lang;
        $paste->code = $code;
        $paste->save();

        return json([
            'status' => 'success',
            'msg' => '提交成功',
            'data' => $paste
        ]);
    }
}