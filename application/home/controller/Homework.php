<?php


namespace app\home\controller;


use app\api\model\ContestModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Homework extends UserBaseController {

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'homeworks');
    }

    public function index($keyword = '') {
        $like_str = '%%';
        if ('' != $keyword) {
            $l = explode(' ', $keyword);
            $like_str = '%';
            foreach ($l as $item) {
                $like_str .= "{$item}%";
            }
        }

        $order_str = "start_time < '" . date('Y-m-d H:i:s') . "',end_time > '" . date('Y-m-d H:i:s') . "' desc, start_time desc";
        $contests = ContestModel::where('type', 1)
            ->where('defunct', 'N')
            ->where('title', 'like', $like_str)
            ->orderRaw($order_str)
            ->paginate(10);
        if ('' != $keyword) $contests->appends('keyword', $keyword);
        $this->assign('contests', $contests);
        $this->assign('title_val', $this->lang['homework']);
        return view($this->theme_root . '/contests', ['keyword' => htmlspecialchars($keyword), 'target' => '/homeworks']);
    }

}