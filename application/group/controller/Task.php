<?php


namespace app\group\controller;


use app\api\model\ContestModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupTaskModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\group\common\GroupBaseController;
use think\Db;

class Task extends GroupBaseController {

    /**
     * Group task page
     *
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function show_group_tasks() {
        $this->assign('nav', 'tasks');

        $members = GroupJoinModel::all(['group_id' => $this->group->id]);
        foreach ($members as $member) {
            $member->realname = UserModel::get(['user_id' => $member->user_id])->realname;
        }

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
            $task->contest->login_user_ac_cnt = Db::query("select count(DISTINCT problem_id) as cnt from solution where contest_id=" . $task->contest_id . " and user_id='" . $this->login_user->user_id . "' and result=4")[0]['cnt'];

            $task->ac_member_cnt = (new SolutionModel())
                ->where('contest_id', $task->contest_id)
                ->where('result', 4)
                ->whereIn('user_id', $user_ids)
                ->count('distinct user_id');

        }
        $this->assign('tasks', $tasks);
        return view($this->theme_root . '/group-tasks');
    }
}