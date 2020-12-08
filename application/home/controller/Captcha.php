<?php


namespace app\home\controller;


use app\home\common\HomeBaseController;
use Gregwar\Captcha\CaptchaBuilder;

class Captcha extends HomeBaseController {
    public function index() {
        $builder = new CaptchaBuilder();
        $builder->build();
        session('captcha', strtolower($builder->getPhrase()));
        return response($builder->output())->header('Content-Type', 'image/jpeg');
    }
}