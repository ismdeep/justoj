<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/7
 * Time: 12:00 AM
 */

namespace app\extra\controller;


use think\Request;

class ApiBaseController extends BaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        config('app_debug', false);
        config('app_trace', false);
    }
}