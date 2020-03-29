<?php
/**
 * User: ismdeep
 * Date: 2019-07-08 10:56
 */

namespace app\api\controller;


use app\extra\controller\ApiBaseController;

class Theme extends ApiBaseController {
    public function select_theme($theme = 'bootstrap') {
        session('theme_root', 'extra@themes/' . $theme);
        return json([
            'code' => 0,
            'msg' => '操作成功',
            'data' => $theme
        ]);
    }
}