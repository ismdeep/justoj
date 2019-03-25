<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 21:14
 */

namespace app\login\controller;


use app\extra\controller\UserBaseController;
use think\Controller;

class Index extends UserBaseController
{
    public function index ($redirect='/')
    {
    	$this->assign('redirect', $redirect);
        return view($this->theme_root . '/login');
    }
}