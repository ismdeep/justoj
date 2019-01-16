<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 19:59
 */

namespace app\extra\controller;


use think\Controller;
use think\Request;
use think\Session;

class AdminBaseController extends BaseController
{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if (!session('administrator'))
        {
            return $this->redirect('/');
        }
    }
}