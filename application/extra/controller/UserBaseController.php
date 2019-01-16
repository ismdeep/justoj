<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 22:44
 */

namespace app\extra\controller;

use think\Request;

class UserBaseController extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
}