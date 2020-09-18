<?php


namespace app\group\controller;


use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use app\api\model\GroupTaskModel;
use app\api\model\PrivilegeModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\group\common\GroupBaseController;
use think\Db;
use think\Exception;

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

    public function create_homework_page() {
        intercept(!$this->is_group_manager, 'Access Denied');
        return view($this->theme_root . '/group-task-create');
    }

    /**
     * @param string $title
     * @param string $start_time
     * @param string $end_time
     * @param string $description
     * @param string $problem_ids
     * @return \think\response\Json
     */
    public function create_homework_page_json($title = '', $start_time = '', $end_time = '', $description = '', $problem_ids = '') {
        if (!$this->is_group_manager) {
            return json(['code' => 500, 'msg' => 'Access Denied']);
        }

        /* 判断title */
        if ('' == $title) return json(['status' => 'error', 'msg' => '标题不可为空']);
        // 判断begin_time和end_time
        $patten = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";
        if (!preg_match($patten, $start_time)) {
            return json(['status' => 'error', 'msg' => '请选择开始时间']);
        }

        if (!preg_match($patten, $end_time)) {
            return json(['status' => 'error', 'msg' => '请选择结束时间']);
        }


        // 判断problem_ids是否合法
        if (strchr($problem_ids, '，')) {
            return json(['status' => 'error', 'msg' => '请使用英文逗号,']);
        }

        // 判断这些题目是否都存在
        $pids = explode(',', $problem_ids);
        $problems = array();
        foreach ($pids as $pid) {
            $problem = ProblemModel::get(['problem_id' => $pid]);
            if (!$problem) {
                return json(['status' => 'error', 'msg' => 'Problem not exists. id: ' . $pid]);
            }
            array_push($problems, $problem);
        }

        // 创建比赛
        $contest = new ContestModel();
        $contest->title = $title;
        $contest->start_time = $start_time;
        $contest->end_time = $end_time;
        $contest->defunct = 'N';
        $contest->description = $description;
        $contest->private = 0;
        $contest->type = ContestModel::TYPE_HOMEWORK;
        $contest->save();

        $problem_index = 0;
        foreach ($problems as $p) {
            $contest_problem = new ContestProblemModel();
            $contest_problem->problem_id = $p->problem_id;
            $contest_problem->contest_id = $contest->contest_id;
            $contest_problem->num = $problem_index;
            $contest_problem->save();
            $problem_index++;
        }

        // 赋予当前用户于比赛管理权限
        $privilege = new PrivilegeModel();
        $privilege->user_id = $this->login_user->user_id;
        $privilege->rightstr = 'm' . $contest->contest_id;
        $privilege->defunct = 'N';
        $privilege->save();

        // 关联比赛与班级
        $group_task = new GroupTaskModel();
        $group_task->group_id = $this->group->id;
        $group_task->title = $title;
        $group_task->contest_id = $contest->contest_id;
        $group_task->save();

        return json([
            'code' => 0,
            'msg' => 'success'
        ]);
    }

    public function copy_homeworks_from_group() {
        intercept(!$this->is_group_manager, 'Access Denied');
        return view($this->theme_root . '/group-task-copy-group');
    }

    public function copy_homeworks_from_group_json($group_id = '', $start_time = '', $end_time = '') {
        if (!$this->is_group_manager) {
            return json(['code' => 500, 'msg' => 'Access Denied']);
        }

        /* 判断begin_time和end_time */
        $patten = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";
        if (!preg_match($patten, $start_time)) {
            return json(['code' => 500, 'msg' => '请选择开始时间']);
        }

        if (!preg_match($patten, $end_time)) {
            return json(['code' => 500, 'msg' => '请选择结束时间']);
        }

        try {
            $this->group->copy_tasks_from_group($group_id, $start_time, $end_time);
        } catch (Exception $e) {
            return json(['code' => 404, 'msg' => $e->getMessage()]);
        }

        return json(['code' => 0, 'msg' => '添加成功']);
    }

    public function copy_homework_from_homework() {
        intercept(!$this->is_group_manager, 'Access Denied');
        return view($this->theme_root . '/group-task-copy-homework');
    }

    /**
     * 从作业复制作业到班级
     *
     * @param string $homework_id
     * @param string $start_time
     * @param string $end_time
     * @return \think\response\Json
     */
    public function copy_homework_from_homework_json($homework_id = '', $start_time = '', $end_time = '') {
        if (!$this->is_group_manager) {
            return json(['code' => 500, 'msg' => 'Access Denied']);
        }

        /* 判断begin_time和end_time */
        $patten = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";
        if (!preg_match($patten, $start_time)) {
            return json(['code' => 500, 'msg' => '请选择开始时间']);
        }

        if (!preg_match($patten, $end_time)) {
            return json(['code' => 500, 'msg' => '请选择结束时间']);
        }

        try {
            $this->group->copy_task_from_homework($homework_id, $start_time, $end_time);
        } catch (Exception $e) {
            return json(['code' => 404, 'msg' => $e->getMessage()]);
        }

        return json([
            'code' => 0,
            'msg' => 'success'
        ]);
    }

}