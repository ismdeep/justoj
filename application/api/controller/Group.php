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

        return json(['status' => 'success']);
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

    /**
     * 搜索班级
     *
     * @param string $search_key
     */
    public function search_json($search_key = '') {
        $groups = (new GroupModel())->where('name', 'like', "%{$search_key}%")
            ->whereOr('id', $search_key)
            ->limit(10)
            ->select();
        foreach ($groups as $group) {
            /* @var $group GroupModel */
            $group->password = '******';
        }
        return json(['code' => 0, 'data' => $groups]);
    }

}