<?php


namespace app\home\controller;


use app\api\model\ContestEnrollModel;
use app\api\model\ContestModel;
use app\api\model\NewsModel;
use app\home\common\HomeBaseController;

class Index extends HomeBaseController {

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
        $this->assign('newsList', (new NewsModel())->where('defunct', 'N')->order('id', 'desc')->select());

        // 近期比赛显示功能
        $recent_contests = (new ContestModel())
            ->where('end_time', '>', strftime("%Y-%m-%d %H:%M:%S", time()))
            ->where('type', 0) // 0 表示比赛
            ->where('defunct', 'N') // N 表示启用
            ->order('start_time', 'asc')
            ->select();

        foreach ($recent_contests as $contest) {
            $contest->started = $contest->start_time < date('Y-m-d H:i:s', time());
            $contest->rolled = $this->login_user && (new ContestEnrollModel())
                ->where('user_id', $this->login_user->user_id)
                ->where('contest_id', $contest->contest_id)
                ->find();
        }

        $this->assign('recent_contests', $recent_contests);
        return view($this->theme_root . '/index');
    }
}