<?php


namespace app\home\controller;


use app\extra\controller\UserBaseController;
use Gregwar\Captcha\CaptchaBuilder;

class Captcha extends UserBaseController {
    public function index() {
        $builder = new CaptchaBuilder();
        $builder->build();
        session('captcha', strtolower($builder->getPhrase()));
        return response($builder->output())->header('Content-Type', 'image/jpeg');
    }
}