<?php

namespace app\admin\controller;

use app\api\model\ContestModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\extra\controller\AdminBaseController;
use think\App;
use function Sodium\version_string;

class Index extends AdminBaseController
{
    public function index()
    {
        return view();
    }

    public function index2()
    {
        return view();
    }

    public function welcome()
    {
        // 题目数量
        $problem_count = (new ProblemModel())->count();
        // 比赛数量
        $contest_count = (new ContestModel())->where(['type' => 0])->count();
        // 作业数量
        $homework_count = (new ContestModel())->where(['type' => 1])->count();
        // 用户数量
        $user_count = (new UserModel())->count();
        // 提交数量
        $solution_count = (new SolutionModel())->count();
        // AC数量
        $ac_count = (new SolutionModel())->where(['result' => 4])->count();

        // / 硬盘剩余信息
        $free_space = round(disk_free_space('/') / 1024 / 1024 / 1024, 2);

        // /home 硬盘剩余信息
        $free_space_home = round(disk_free_space('/home') / 1024 / 1024 / 1024, 2);

        $this->assign('problem_count', $problem_count);
        $this->assign('contest_count', $contest_count);
        $this->assign('homework_count', $homework_count);
        $this->assign('solution_count', $solution_count);
        $this->assign('user_count', $user_count);
        $this->assign('ac_count', $ac_count);
        $this->assign('free_space', $free_space);
        $this->assign('free_space_home', $free_space_home);
        $this->assign('thinkphp_version', THINK_VERSION);

        return view();
    }
}
