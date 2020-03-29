<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/13
 * Time: 10:41 PM
 */

namespace app\group\controller;


use app\api\model\ContestModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupTaskModel;
use app\api\model\SolutionModel;
use app\extra\controller\GroupBaseController;
use think\Db;
use think\Request;

class Tasks extends GroupBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'tasks');
    }

    /**
     * Group task page
     *
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function index() {
        $tasks = GroupTaskModel::all(['group_id' => $this->group->id]);

        /* 获取总人数和完成作业的人数 */
        $user_ids = [];
        $users = (new GroupJoinModel())
            ->where('group_id', $this->group->id)
            ->select();
        foreach ($users as $user) {
            $user_ids [] = $user->user_id;
        }
        $group_member_cnt = sizeof($users);

        $this->assign('group_member_cnt', $group_member_cnt);

        foreach ($tasks as $task) {
            $task->contest = ContestModel::get(['contest_id' => $task->contest_id]);
            // 获取这个比赛题目数量
            $task->contest->problem_cnt = Db::query("select count(problem_id) as cnt from contest_problem where contest_id=" . $task->contest_id)[0]['cnt'];
            // 获得登录用户A题数量
            $task->contest->loginuser_ac_cnt = Db::query("select count(DISTINCT problem_id) as cnt from solution where contest_id=" . $task->contest_id . " and user_id='" . $this->loginuser->user_id . "' and result=4")[0]['cnt'];

            $task->ac_member_cnt = (new SolutionModel())
                ->where('contest_id', $task->contest_id)
                ->where('result', 4)
                ->whereIn('user_id', $user_ids)
                ->count('distinct user_id');

        }
        $this->assign('tasks', $tasks);
        return view($this->theme_root . '/group-tasks');
    }

    /**
     * 下载班级作业excel表格
     */
    public function download_excel($contest_id) {
    }
}
