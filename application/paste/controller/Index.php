<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/9/20
 * Time: 10:05 PM
 */

namespace app\paste\controller;


use app\api\model\PasteModel;
use app\extra\controller\BaseController;

class Index extends BaseController
{
    public function index($id = '')
    {
        $this->assign('nav', 'paste');
        if ('' == $id) {
            $this->assign('allowed_langs', paste_allowed_langs());
            return view('index');
        }

        $paste = (new PasteModel())->where('id', $id)->find();
        intercept(null == $paste, '代码不存在');

        $paste->code = htmlspecialchars($paste->code);
        $paste->lang_text = paste_allowed_langs()[$paste->lang];

        $this->assign('paste', $paste);
        return view('detail');
    }
}