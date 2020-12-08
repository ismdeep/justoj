<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/7
 * Time: 19:59
 */

namespace app\extra\controller;


use think\Controller;
use think\Request;
use think\Session;

class AdminBaseController extends BaseController {

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $passed = false;
        $passed = $this->login_user && $this->login_user->is_admin ? true : $passed;
        $passed = $this->login_user && $this->login_user->is_root ? true : $passed;
        if (!$passed) {
            $this->redirect('/');
        }
    }
}