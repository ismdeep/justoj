<?php


namespace app\api\common;


use think\Env;
use think\Request;

class JudgeApiBaseController extends ApiBaseController {

    public $client_name = 'unknown';

    public function __construct(Request $request = null) {
        parent::__construct($request);

        $secure_code = $request->param('secure_code');
        $this->client_name = $request->param('client_name');

        intercept(Env::get('config.secure_code') != $secure_code, '0');
    }
}