<?php


namespace app\api\common;


use app\common\controller\BaseController;
use think\Request;

class ApiBaseController extends BaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        config('app_debug', false);
        config('app_trace', false);
    }
}