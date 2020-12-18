<?php


namespace app\group\controller;


use app\api\model\ContestModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupTaskModel;
use app\api\model\PrivilegeModel;
use app\api\model\ProblemModel;
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

        foreach ($tasks as $task) {
            $task->contest = ContestModel::get(['contest_id' => $task->contest_id]);
            /* 获取这个比赛题目数量 */
            $task->contest->problem_cnt = $task->getProblemCnt();
            /* 获得登录用户AC题目数量 */
            $task->contest->login_user_ac_cnt = $task->getDoneCntByUserId($this->login_user->user_id);
            /* 获取班级平均AC题目数量 */
            $task->contest->total_ac_cnt = $task->getTotalDoneCnt();
            $task->contest->avg_ac_cnt = 0;
            if ($group_member_cnt > 0) {
                $task->contest->avg_ac_cnt = round($task->contest->total_ac_cnt * 1.0 / $group_member_cnt, 1);
            }

            $task->avg_progress_html = "<div class='progress-bar'>0/0</div>";
            $task->login_user_progress_html = "<div class='progress-bar'>0/0</div>";

            if ($task->contest->problem_cnt > 0) {
                $process_bar_style = 'progress-bar-info';
                $login_user_ac_rate = round($task->contest->login_user_ac_cnt * 100.00 / $task->contest->problem_cnt, 2);
                if ($login_user_ac_rate >= 100.0) {
                    $login_user_ac_rate = 100.0;
                    $process_bar_style = 'progress-bar-success';
                }

                $task->login_user_progress_html = "<div class='progress-bar {$process_bar_style}' style='width: {$login_user_ac_rate}%;'>{$task->contest->login_user_ac_cnt}/{$task->contest->problem_cnt}</div>";
            }

            if ($task->contest->problem_cnt > 0 && $group_member_cnt > 0) {
                $process_bar_style = 'progress-bar-info';
                $avg_rate = round($task->contest->total_ac_cnt * 100.00 / ($task->contest->problem_cnt * $group_member_cnt), 2);
                if ($avg_rate >= 100.0) {
                    $avg_rate = 100.0;
                    $process_bar_style = 'progress-bar-success';
                }
                $task->avg_progress_html = "<div class='progress-bar {$process_bar_style}' style='width: {$avg_rate}%;'>{$task->contest->avg_ac_cnt}/{$task->contest->problem_cnt}</div>";
            }
        }
        $this->assign('tasks', $tasks);
        return view('./group-tasks', [
            'group_member_cnt' => $group_member_cnt
        ]);
    }

    public function create_homework_page() {
        intercept(!$this->is_group_manager, 'Access Denied');
        return view('./group-task-create');
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
        $homework->creator_id = $this->login_user->user_id;
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
        return view('./group-task-copy-group');
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
        return view('./group-task-copy-homework');
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