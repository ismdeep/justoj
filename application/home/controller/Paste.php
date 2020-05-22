<?php


namespace app\home\controller;


use app\api\model\PasteModel;
use app\extra\controller\UserBaseController;

class Paste extends UserBaseController {

    public function index() {
        $this->assign('nav', 'paste');
        $this->assign('allowed_langs', paste_allowed_langs());
        return view($this->theme_root . '/paste');
    }

    public function show_paste_detail($id) {
        $this->assign('nav', 'paste');
        $paste = (new PasteModel())->where('id', $id)->find();
        intercept(null == $paste, '代码不存在');

        $paste->code = htmlspecialchars($paste->code);
        $paste->lang_text = paste_allowed_langs()[$paste->lang];

        $this->assign('paste', $paste);
        return view($this->theme_root . '/paste-detail');
    }
}