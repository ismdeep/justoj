<?php


namespace app\home\controller;


use app\api\model\ContestModel;
use app\api\model\NewsModel;
use app\extra\controller\UserBaseController;

class Index extends UserBaseController {

    /***
     * JustOJ Home Page Controller
     *
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index() {
        // 获取新闻列表
        $this->assign('newss', NewsModel::where('defunct', 'N')->order('time', 'desc')->select());

        // 近期比赛显示功能
        $recent_contests = (new ContestModel())
            ->whereBetween('start_time', [strftime("%Y-%m-%d %H:%M:%S", time()), '2219-11-11 00:00:00'])
            ->where('type', 0)
            ->order('start_time', 'asc')
            ->select();

        $this->assign('recent_contests', $recent_contests);

        return view($this->theme_root . '/index');
    }

}