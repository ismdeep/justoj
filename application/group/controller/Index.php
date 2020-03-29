<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/11
 * Time: 11:01
 */

namespace app\group\controller;


use app\extra\controller\GroupBaseController;
use think\Model;
use think\Request;

class Index extends GroupBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'home');
    }

    /**
     * 班级首页
     */
    public function index() {
        return view($this->theme_root . '/group');
    }
}