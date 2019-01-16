<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/7/29
 * Time: 4:59 PM
 */

namespace app\status\controller;


use app\extra\controller\BaseController;

class Langs extends BaseController
{
    public function index()
    {
        return view('justoj-language-usage-demo');
    }
}