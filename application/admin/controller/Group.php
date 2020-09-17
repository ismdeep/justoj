<?php

namespace app\admin\controller;

use app\api\controller\Solution;
use app\api\model\ContestEnrollModel;
use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\ContestTouristModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use app\api\model\GroupTaskModel;
use app\api\model\SolutionModel;
use app\extra\controller\AdminBaseController;

class Group extends AdminBaseController {
    public function group_list() {
        return view();
    }


    /**
     * @param int $page
     * @param int $limit
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function group_list_json($page = 1, $limit = 10) {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $where = [
            'owner_id' => $this->login_user->user_id,
            'deleted' => 0
        ];
        $groups = (new GroupModel())->where($where)->limit(($page - 1) * $limit, $limit)->select();
        $count = (new GroupModel())->where($where)->count();
        foreach ($groups as $group) {
            $group->fk();
        }
        return json([
            'code' => 0,
            'count' => $count,
            'data' => $groups
        ]);
    }

    public function add() {
        $group = new GroupModel();
        $group->id = '';
        $group->name = '';
        $group->owner_id = $this->login_user->user_id;
        $group->type = 0;
        $group->password = '';
        $group->description = '';
        $this->assign('group', $group);
        return view('edit');
    }

    public function edit($id = '') {
        intercept('' == $id, 'id参数错误');
        $id = intval($id);
        $group = (new GroupModel())->where('id', $id)->find();
        $this->assign('group', $group);
        return view('edit');
    }

    /**
     * 新增/修改班级 json接口
     *
     * @param string $group_id
     * @param string $group_name
     * @param string $type
     * @param string $group_password
     * @param string $description
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save_json($group_id = '', $group_name = '', $type = '', $group_password = '', $description = '') {
        intercept_json('' == $type, '请选择类型');
        $type = intval($type);
        intercept_json(1 == $type && '' == $group_password, '私有班级的密码不可为空。');

        if ('' == $group_id) {
            $group = new GroupModel();
            $group->owner_id = $this->login_user->user_id;
        } else {
            $group_id = intval($group_id);
            $group = (new GroupModel())->where('id', $group_id)->find();
            intercept_json(null == $group, '班级不存在');
        }
        $group->name = $group_name;
        $group->type = $type;
        $group->password = $group_password;
        $group->description = $description;
        $group->save();

        return json([
            'status' => 'success',
            'msg' => '保存成功'
        ]);
    }

    /**
     * Delete Group API (Through deleted column)
     *
     * @param string $group_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delete_json($group_id = '') {
        intercept_json('' == $group_id, 'group_id参数错误');
        $group_id = intval($group_id);
        $group = (new GroupModel())->where('id', $group_id)->find();
        intercept_json(null == $group, '班级不存在');
        $group->deleted = 1;
        $group->save();

        (new GroupJoinModel())
            ->where('group_id', $group->id)
            ->update(['deleted' => 1]);

        return json([
            'status' => 'success',
            'msg' => '已删除'
        ]);
    }

    /**
     * 删除班级作业
     *
     * @param string $group_task_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function delete_task_json($group_task_id = '') {
        intercept_json('' == $group_task_id, 'argv error');
        $group_task_id = intval($group_task_id);
        $group_task = (new GroupTaskModel())->where('id', $group_task_id)->find();
        intercept_json(null == $group_task, 'Group Task Not Found.');
        $group = (new GroupModel())->where('id', $group_task->group_id)->find();
        intercept_json(null == $group, 'Group Not Found.');
        intercept_json($this->login_user->user_id != $group->owner_id, 'PERMISSION DENIED.');

        $contest = (new ContestModel())
            ->where('contest_id', $group_task->contest_id)
            ->find();

        (new ContestEnrollModel())
            ->where('contest_id', $contest->contest_id)
            ->delete();

        (new ContestProblemModel())
            ->where('contest_id', $contest->contest_id)
            ->delete();

        (new ContestTouristModel())
            ->where('contest_id', $contest->contest_id)
            ->delete();

        (new SolutionModel())
            ->where('contest_id', $contest->contest_id)
            ->delete();

        (new ContestModel())
            ->where('contest_id', $contest->contest_id)
            ->delete();

        (new GroupTaskModel())
            ->where('id', $group_task_id)
            ->delete();


        return json([
            'status' => 'success',
            'msg' => '已删除'
        ]);
    }
}

