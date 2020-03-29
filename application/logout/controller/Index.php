<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 11:39 PM
 */

namespace app\logout\controller;


use app\extra\controller\UserBaseController;
use think\Session;

class Index extends UserBaseController {
    public function index($redirect = '/') {
        Session::destroy();
        $this->redirect($redirect);
    }
}