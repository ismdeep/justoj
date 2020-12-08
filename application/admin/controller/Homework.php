<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/6/1
 * Time: 22:39
 */

namespace app\admin\controller;

use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\admin\common\AdminBaseController;

class Homework extends AdminBaseController {

    public function homework_list() {
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
    public function homework_list_json($page = 1, $limit = 10) {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $where = ['type' => 1];
        $contests = (new ContestModel())->where($where)->order('contest_id', 'desc')->limit(($page - 1) * $limit, $limit)->select();
        $count = (new ContestModel())->where($where)->count();
        return json([
            'code' => 0,
            'count' => $count,
            'data' => $contests
        ]);
    }

    /**
     * Clone作业
     * @param $from_contest_id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function clone_homework($from_contest_id = null) {
        intercept(null == $from_contest_id, 'from_contest_id为空');
        // 判断是否管理员
        if (!$this->login_user || !$this->login_user->is_admin) {
            return view('admin@layout/error', ['error_msg' => $this->lang['do_not_have_privilege']]);
        }

        // 判断from_contest_id是否存在
        $contest = ContestModel::get(['contest_id' => $from_contest_id]);
        if (!$contest) {
            return view('admin@layout/error', ['error_msg' => $this->lang['contest_not_exists']]);
        }

        // 获取题目列表
        $contest_problems = ContestProblemModel::where('contest_id', $from_contest_id)->order('num', 'asc')->select();
        $flag = false;
        $problem_ids = '';
        foreach ($contest_problems as $contest_problem) {
            if ($flag) {
                $problem_ids .= ',';
            }
            $flag = true;
            $problem_ids .= $contest_problem->problem_id;
        }
        $contest->problem_ids = $problem_ids;

        // 更改一下contest的标题
        $contest->title = $contest->title . '(clone)';

        // 更改一下contest的contest_id
        $contest->contest_id = '';

        return view('edit', ['contest' => $contest]);
    }

    /**
     * 添加作业
     */
    public function add() {
        $contest = new ContestModel();
        $contest->contest_id = '';
        $contest->title = '';
        $contest->private = 0;
        $contest->password = '';
        $contest->start_time = date('Y-m-d H:00:00', time() + 60 * 60);
        $contest->end_time = date('Y-m-d H:00:00', time() + 6 * 60 * 60);
        $contest->description = '';
        $contest->problem_ids = '';
        return view('edit', ['contest' => $contest]);
    }

    /**
     * 修改比赛权限
     * @param $contest_id
     * @param null $private
     * @param null $password
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function change_privileges($contest_id = null, $private = null, $password = null) {
        $this->not_null($contest_id, 'contest_id can not be null');
        $contest = ContestModel::get(['contest_id' => $contest_id]);
        $this->not_null($contest, 'contest is not exists');
        $this->need_admin();
        $this->not_null($private, 'private value can not be null');
        if (0 == $private) {
            $contest->private = 0;
            $contest->password = '';
            $contest->save();
            return json(['status' => 'success']);
        }

        if (1 == $private) {
            $this->not_null($password, 'password can not be null');
            $contest->private = 1;
            $contest->password = $password;
        }
    }
}
