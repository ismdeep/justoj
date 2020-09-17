<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/11
 * Time: 8:58 PM
 */

namespace app\api\controller;


use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\GroupAnnounceModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use app\api\model\GroupTaskModel;
use app\api\model\PrivilegeModel;
use app\api\model\ProblemModel;
use app\extra\controller\ApiBaseController;

class Group extends ApiBaseController {
    /**
     * 用户加入班级api接口
     *
     * @param $group_id
     * @param string $password
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function join($group_id, $password = '') {
        // 判断班级是否存在
        $group = GroupModel::get(['id' => $group_id]);
        if (!$group) {
            return json(['status' => 'error', 'msg' => $this->lang['group_not_exists']]);
        }
        // 判断用户是否登录
        if (!$this->login_user) {
            return json(['status' => 'error', 'msg' => $this->lang['not_login']]);
        }
        // 判断用户是否为此班级管理员
        if ($this->login_user->user_id == $group->owner_id) {
            return json(['status' => 'error', 'msg' => $this->lang['you_are_the_manager_of_this_group']]);
        }
        // 判断用户是否已经有加入此班级记录
        if (GroupJoinModel::get(['group_id' => $group_id, 'user_id' => $this->login_user->user_id])) {
            return json(['status' => 'error', 'msg' => $this->lang['you_have_request_join_the_group_already']]);
        }

        // 添加加入申请(对班级进行判断，如果type为0公开则直接加入(status为1)，如果type为1私有status为0)
        if (0 == $group->type) {
            $group_join = new GroupJoinModel();
            $group_join->user_id = $this->login_user->user_id;
            $group_join->group_id = $group_id;
            $group_join->status = 1;
            $group_join->save();
            return json(['status' => 'success']);
        }

        // 剩下部分都是当1 == $group->type (group是私有的)
        if ('' == $group->password) {
            $group_join = new GroupJoinModel();
            $group_join->user_id = $this->login_user->user_id;
            $group_join->group_id = $group_id;
            $group_join->status = 0;
            $group_join->save();
            return json(['status' => 'warn', 'msg' => '请等待管理员审核。']);
        }

        if ($password == $group->password) {
            $group_join = new GroupJoinModel();
            $group_join->user_id = $this->login_user->user_id;
            $group_join->group_id = $group_id;
            $group_join->status = 1;
            $group_join->save();
            return json(['status' => 'success']);
        }

        return json(['status' => 'error', 'msg' => $this->lang['wrong_password']]);
    }


    /**
     * 管理员修改学生加入班级状态
     *
     * TODO 未完成
     */
    public function group_join_status_change($group_join_id, $status) {
        // 判断]group_join是否存在
        $group_join = GroupJoinModel::get(['id' => $group_join_id]);
        if (!$group_join) return json(['status' => 'error', 'msg' => $this->lang['group_join_not_exists']]);

        // 判断group是否存在
        $group = GroupModel::get(['id' => $group_join->group_id]);
        if (!$group) return json(['status' => 'error', 'msg' => $this->lang['group_not_exists']]);

        // 判断当前用户是否是班级管理员
        if (!($this->login_user && $this->login_user->user_id == $group->owner_id)) return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);

        // 写入status
        $group_join->status = $status;
        $group_join->save();
        return json(['status' => 'success']);
    }


    /**
     * 创建班级
     * @param $group_id
     * @param $name
     * @param $privilege
     * @param $password
     * @param $description
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function save($group_id, $name, $privilege, $password, $description) {
        if (!$this->is_administrator) return json(['status' => 'error', 'msg' => $this->lang['dont_have_privilege']]);

        $group = null;
        if ('' != $group_id) {
            $group = GroupModel::get(['id' => $group_id]);
            if (!$group) return json(['status' => 'error', 'msg' => 'group not exists']);
            if ($group->owner_id != $this->login_user->user_id) return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
        } else {
            $group = new GroupModel();
        }
        $group->name = $name;
        $group->owner_id = $this->login_user->user_id;
        $group->type = $privilege;
        $group->password = $password;
        $group->description = $description;
        $group->save();

        // 给当前用户添加一个加入班级之记录
//		$group_join = new GroupJoinModel();
//		$group_join->user_id = $this->login_user->user_id;
//		$group_join->group_id = $group->id;
//		$group_join->status = 1;
//		$group_join->save();

        return json(['status' => 'success']);
    }


    /**
     * 添加比赛作业
     * @param $group_id
     * @param $title
     * @param $begin_time
     * @param $end_time
     * @param $description
     * @param $problem_ids
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function add_task($group_id, $title, $begin_time, $end_time, $description, $problem_ids) {
        // 判断group_id
        $group = GroupModel::get(['id' => $group_id]);
        if (!$group) return json(['status' => 'error', 'msg' => 'Group not found.']);
        // 判断当前用户是否登录已经是否为此班级之管理员
        if (!$this->login_user) return json(['status' => 'error', 'msg' => $this->lang['dont_have_privilege']]);
        if ($group->owner_id != $this->login_user->user_id) return json(['status' => 'error', 'msg' => $this->lang['dont_have_privilege']]);
        // 判断title
        if ('' == $title) return json(['status' => 'error', 'msg' => '标题不可为空']);
        // 判断begin_time和end_time
        $patten = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";
        if (!preg_match($patten, $begin_time)) {
            return json(['status' => 'error', 'msg' => '请选择开始时间']);
        }

        if (!preg_match($patten, $begin_time)) {
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
        $contest->start_time = $begin_time;
        $contest->end_time = $end_time;
        $contest->defunct = 'N';
        $contest->description = $description;
        $contest->private = 0;
        $contest->type = 1;
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
        $group_task->group_id = $group_id;
        $group_task->title = $title;
        $group_task->contest_id = $contest->contest_id;
        $group_task->save();

        return json(['status' => 'success', 'msg' => 'success']);
    }

    /**
     * 班级详情信息
     *
     * @param $group_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function details($group_id) {
        $group = GroupModel::get(['id' => $group_id]);
        if (!$group) return json(['status' => 'error', 'msg' => 'group not exists']);
        return json(['status' => 'success', 'data' => $group]);
    }

    /**
     * 删除学生加入班级之记录
     *
     * @param $group_join_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function remove_group_join($group_join_id) {
        if (!$this->is_administrator) {
            return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
        }

        // 判断加入记录是否存在
        $group_join = GroupJoinModel::get(['id' => $group_join_id]);
        if (!$group_join) return json(['status' => 'error', 'msg' => 'group join not exists']);

        // 判断班级是否存在
        $group = GroupModel::get(['id' => $group_join->group_id]);
        if (!$group) return json(['status' => 'error', 'msg' => 'group not exists']);

        // 判断是否有操作权限
        if ($group->owner_id != $this->login_user->user_id)
            return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);

        $group_join->delete();

        return json(['status' => 'success', 'msg' => 'delete success']);
    }

    /**
     * 保存班级公告
     *
     * @param $group_id
     * @param $notification_id
     * @param $title
     * @param $content
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function save_notification($group_id, $notification_id, $title, $content) {
        // 判断用户是否登录
        if (!$this->login_user) return json(['status' => 'error', 'msg' => $this->lang['not_login']]);
        // 判断班级是否存在
        $group = GroupModel::get(['id' => $group_id]);
        if (!$group) return json(['status' => 'error', 'msg' => $this->lang['group_not_exists']]);
        // 判断当前用户是否为此班级管理员
        if ($group->owner_id != $this->login_user->user_id)
            return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
        // 判断notification_id是否为空，空则表示新建一个公告，不为空则取出公告并判断公告是否存在
        $notification = null;
        if ('' == $notification_id) {
            $notification = new GroupAnnounceModel();
            $notification->group_id = $group_id;
        } else {
            $notification = GroupAnnounceModel::get(['id' => $notification_id]);
            if (!$notification)
                return json(['status' => 'error', 'msg' => $this->lang['group_notification_not_exists']]);
        }

        // 写入数据
        $notification->title = $title;
        $notification->msg = $content;
        $notification->save();
        return json(['status' => 'success', 'msg' => 'msg']);
    }

    /**
     * 删除班级公告
     *
     * @param $notification_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function remove_notification($notification_id) {
        // 判断用户是否登录
        if (!$this->login_user) return json(['status' => 'error', 'msg' => $this->lang['not_login']]);

        $notification = GroupAnnounceModel::get(['id' => $notification_id]);
        if (!$notification)
            return json(['status' => 'error', 'msg' => $this->lang['group_notification_not_exists']]);

        // 判断班级是否存在
        $group = GroupModel::get(['id' => $notification->group_id]);
        if (!$group) return json(['status' => 'error', 'msg' => $this->lang['group_not_exists']]);

        // 判断当前用户是否为此班级管理员
        if ($group->owner_id != $this->login_user->user_id)
            return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);

        $notification->delete();

        return json(['status' => 'success']);
    }

    /**
     * 查看班级公告详情
     *
     * @param $notification_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function notification_details($notification_id) {
        $notification = GroupAnnounceModel::get(['id' => $notification_id]);
        return json(['status' => 'success', 'data' => $notification]);
    }
}