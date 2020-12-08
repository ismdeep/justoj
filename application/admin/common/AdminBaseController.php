<?php


namespace app\admin\common;


use app\common\controller\BaseController;
use think\Request;

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