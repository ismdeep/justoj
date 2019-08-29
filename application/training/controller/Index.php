<?php

namespace app\training\controller;

use app\extra\controller\UserBaseController;
use think\Request;

class Index extends UserBaseController
{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->assign('nav', 'training');
    }

    public function index()
    {
        return view($this->theme_root . '/training');
    }
}
