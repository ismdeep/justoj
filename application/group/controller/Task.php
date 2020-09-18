<?php


namespace app\group\controller;


use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupTaskModel;
use app\api\model\PrivilegeModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\group\common\GroupBaseController;
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
            /* 获取这个比赛题目数量 */
            $task->contest->problem_cnt = (new ContestProblemModel())->where('contest_id', $task->contest_id)->count('problem_id');
            /* 获得登录用户AC题目数量 */
            $task->contest->login_user_ac_cnt = (new SolutionModel())
                ->where('contest_id', $task->contest_id)
                ->where('user_id', $this->login_user->user_id)
                ->where('result', SolutionModel::RESULT_AC)
                ->group('problem_id')
                ->distinct('problem_id')
                ->count('problem_id');

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
     * @throws \think\exception\DbException
     */
    public function create_homework_page_json($title = '', $start_time = '', $end_time = '', $description = '', $problem_ids = '') {
        if (!$this->is_group_manager) return json(['code' => 500, 'msg' => 'Access Denied']);

        if ('' == $title) return json(['code' => 500, 'msg' => '标题不可为空']);
        if (!datetime_human_valid($start_time)) return json(['code' => 500, 'msg' => '请选择开始时间']);
        if (!datetime_human_valid($end_time)) return json(['code' => 500, 'msg' => '请选择结束时间']);

        /* 判断这些题目是否都存在 */
        $problem_ids = explode(',', $problem_ids);
        $problems = [];
        foreach ($problem_ids as $problem_id) {
            $problem = ProblemModel::get(['problem_id' => $problem_id]);
            if (!$problem) {
                return json(['status' => 'error', 'msg' => 'Problem not exists. id: ' . $problem_id]);
            }
            $problems [] = $problem;
        }

        /* 创建比赛 */
        $homework = new ContestModel();
        $homework->title = $title;
        $homework->start_time = $start_time;
        $homework->end_time = $end_time;
        $homework->defunct = 'N';
        $homework->description = $description;
        $homework->private = ContestModel::PRIVATE_PUBLIC;
        $homework->type = ContestModel::TYPE_HOMEWORK;
        $homework->save();

        $problem_id_arr = [];
        foreach ($problems as $problem) {
            /* @var $problem ProblemModel */
            $problem_id_arr [] = $problem->problem_id;
        }

        $homework->set_problems($problem_id_arr);

        // 赋予当前用户于比赛管理权限
        $privilege = new PrivilegeModel();
        $privilege->user_id = $this->login_user->user_id;
        $privilege->rightstr = 'm' . $homework->contest_id;
        $privilege->defunct = 'N';
        $privilege->save();

        // 关联比赛与班级
        $group_task = new GroupTaskModel();
        $group_task->group_id = $this->group->id;
        $group_task->title = $title;
        $group_task->contest_id = $homework->contest_id;
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

    /**
     * @param string $group_id
     * @param string $start_time
     * @param string $end_time
     * @return \think\response\Json
     */
    public function copy_homeworks_from_group_json($group_id = '', $start_time = '', $end_time = '') {
        if (!$this->is_group_manager) return json(['code' => 500, 'msg' => 'Access Denied']);

        if (!datetime_human_valid($start_time)) return json(['code' => 500, 'msg' => '请选择开始时间']);
        if (!datetime_human_valid($end_time)) return json(['code' => 500, 'msg' => '请选择结束时间']);

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

        if (!datetime_human_valid($start_time)) return json(['code' => 500, 'msg' => '请选择开始时间']);
        if (!datetime_human_valid($end_time)) return json(['code' => 500, 'msg' => '请选择结束时间']);

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