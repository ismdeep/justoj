<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/9/19
 * Time: 10:06 PM
 */

namespace app\admin\controller;


use app\admin\common\AdminBaseController;
use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\ContestTouristModel;
use app\api\model\ProblemModel;
use app\api\model\UserModel;

use think\Db;

class Contest extends AdminBaseController {
    public function contest_list() {
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
    public function contest_list_json($page = 1, $limit = 10) {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $where = ['type' => 0];
        if (!$this->login_user->is_root) {
            $where['creator_id'] = $this->login_user->user_id;
        }
        $contests = (new ContestModel())->where($where)->order('contest_id', 'desc')->limit(($page - 1) * $limit, $limit)->select();
        $count = (new ContestModel())->where($where)->count();
        return json([
            'code' => 0,
            'count' => $count,
            'data' => $contests
        ]);
    }

    public function change_defunct_json($contest_id = '', $defunct = '') {
        intercept_json('' == $contest_id, 'contest_id参数错误');
        intercept_json('' == $defunct, 'defunct不可为空');
        $contest = (new ContestModel())->where('contest_id', $contest_id)->find();
        intercept_json(null == $contest, '比赛不存在');
        $contest->defunct = $defunct;
        $contest->save();
        return json([
            'status' => 'success',
            'msg' => '操作成功'
        ]);
    }

    /**
     * @param string $contest_id
     * @param string $is_need_enroll
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function change_need_enroll_json($contest_id = '', $is_need_enroll = '') {
        intercept_json('' == $contest_id, 'contest_id参数错误');
        intercept_json('' == $is_need_enroll, 'defunct不可为空');
        /* @var $contest ContestModel */
        $contest = (new ContestModel())->where('contest_id', $contest_id)->find();
        intercept_json(null == $contest, '比赛不存在');
        $contest->is_need_enroll = $is_need_enroll;
        $contest->save();
        return json([
            'status' => 'success',
            'msg' => '操作成功'
        ]);
    }

    public function add() {
        $contest = new ContestModel();
        $contest->contest_id = '';
        $contest->title = '';
        $contest->description = '';
        $contest->start_time = '';
        $contest->end_time = '';
        $contest->problem_ids = '';
        $contest->langmask = '*';
        $contest->private = 0;
        $contest->password = '';
        $contest->type = ContestModel::TYPE_CONTEST; /* 0 比赛    1 作业 */

        $allowed_langs_all = $this->allowed_langs();
        for ($i = 0; $i < sizeof($allowed_langs_all); ++$i) {
            $allowed_langs_all[$i]['allowed'] = true;
        }
        $this->assign('allowed_langs_all', $allowed_langs_all);

        $this->assign('contest', $contest);
        return view('edit');
    }

    public function filter_contests() {
        return view('filter_contests');
    }

    /**
     * Clone 比赛
     *
     * @param $from_contest_id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function clone_contest($from_contest_id = null) {
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

        $allowed_langs_all = $this->allowed_langs();
        for ($i = 0; $i < sizeof($allowed_langs_all); ++$i) {
            $allowed_langs_all[$i]['allowed'] = true;
        }
        $this->assign('allowed_langs_all', $allowed_langs_all);

        return view('edit', ['contest' => $contest]);
    }

    /**
     * @param string $contest_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($contest_id = '') {
        intercept('' == $contest_id, 'contest_id参数不可为空。');
        $contest = (new ContestModel())->where('contest_id', $contest_id)->find();
        $contest_problems = (new ContestProblemModel())->where('contest_id', $contest_id)->order('num', 'asc')->select();
        $problem_ids_arr = [];
        foreach ($contest_problems as $p) $problem_ids_arr[] = $p->problem_id;
        $problem_ids = implode(',', $problem_ids_arr);
        $contest->problem_ids = $problem_ids;

        /**
         * 获取运行使用的语言列表
         */
        $allowed_langs_all = $this->allowed_langs();
        $allowed_lang_ids = explode(',', $contest->langmask);
        for ($i = 0; $i < sizeof($allowed_langs_all); ++$i) {
            $allowed_langs_all[$i]['allowed'] = false;
        }
        if ('*' == $contest->langmask) {
            for ($i = 0; $i < sizeof($allowed_langs_all); ++$i) {
                $allowed_langs_all[$i]['allowed'] = true;
            }
        }

        foreach ($allowed_lang_ids as $allowed_lang_id) {
            for ($i = 0; $i < sizeof($allowed_langs_all); ++$i) {
                if (intval($allowed_lang_id) == intval($allowed_langs_all[$i]['id'])) {
                    $allowed_langs_all[$i]['allowed'] = true;
                }
            }
        }

        $this->assign('allowed_langs_all', $allowed_langs_all);
        $this->assign('contest', $contest);
        return view();
    }

    /**
     * 保存作业
     *
     * @param string $contest_id
     * @param string $title
     * @param string $start_time
     * @param string $end_time
     * @param string $langmask_flag
     * @param string $allowed_langs
     * @param string $description
     * @param string $problem_ids
     * @param int $private
     * @param string $password
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function save_json(
        $contest_id = '',
        $title = '',
        $start_time = '',
        $end_time = '',
        $langmask_flag = '',
        $allowed_langs = '',
        $description = '',
        $problem_ids = '',
        $private = 0,
        $password = '',
        $type = 1
    ) {
        intercept_json('' == $title, '请输入标题');
        intercept_json('' == $start_time, '请选择开始时间');
        intercept_json('' == $end_time, '请选择结束时间');

        // 判断这些题目是否都存在
        $pids = explode(',', $problem_ids);
        $problems = [];
        foreach ($pids as $pid) {
            $problem = ProblemModel::get(['problem_id' => $pid]);
            if (!$problem) {
                return json([
                    'status' => 'error',
                    'msg' => 'Problem not exists. id: ' . $pid
                ]);
            }
            $problems[] = $problem;
        }

        /* 判断contest_id是否存在 */
        $contest = null;
        if ('' == $contest_id) {
            $contest             = new ContestModel(); // 创建比赛
            $contest->creator_id = $this->login_user->user_id;
            $contest->defunct    = 'N';
            $contest->type       = ContestModel::TYPE_HOMEWORK;
        } else {
            /* 判断contest实体是否存在 */
            $contest = ContestModel::get(['contest_id' => $contest_id]);
            if (null == $contest) {
                return json(['status' => 'error', 'msg' => $this->lang['contest_not_exists']]);
            }

            /* 删除contest_problem里面的记录 */
            (new ContestProblemModel())->where('contest_id', $contest_id)->delete();
        }

        /**
         * 写入信息
         */
        $contest->title = $title;
        $contest->start_time = $start_time;
        $contest->end_time = $end_time;
        $contest->description = $description;
        $contest->private = intval($private);

        if (1 == intval($private)) {
            $contest->password = $password;
        } else {
            $contest->password = '';
        }
        /**
         * 写入编程语言信息
         */
        if ('*' == $langmask_flag) {
            $contest->langmask = '*';
        } else {
            $contest->langmask = $allowed_langs;
        }

        $contest->type = intval($type);

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

        return json([
            'status' => 'success',
            'msg' => 'Save successfully',
            'data' => $contest
        ]);
    }

    public function set_contest_type_json($contest_id = '', $type = '') {
        intercept_json('' == $contest_id, 'contest_id不可为空');
        intercept_json('' == $type, 'type不可为空');
        $contest = (new ContestModel())->where('contest_id', $contest_id)->find();
        intercept_json(null == $contest, 'contest不存在');
        $contest->type = intval($type);
        $contest->save();
        return json(['status' => 'success', 'msg' => '操作成功']);
    }


    /**
     * @param string $contest_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function tourist_list_json($contest_id = '') {
        if ('' == $contest_id) {
            return json([
                'code' => 1
            ]);
        }

        $contest_tourists = ContestTouristModel::all(['contest_id' => $contest_id]);

        $users_tmp = Db::query("select DISTINCT user_id from solution where contest_id=" . $contest_id);
        $users = [];
        foreach ($users_tmp as $user_id) {
            $users [] = UserModel::get(['user_id' => $user_id['user_id']]);
        }
        foreach ($users as $user) {
            $user->is_tourist = false;
            foreach ($contest_tourists as $contest_tourist) {
                if ($contest_tourist->user_id == $user->user_id) {
                    $user->is_tourist = true;
                }
            }
        }
        return json([
            'code' => 0,
            'data' => $users,
            'count' => 1000
        ]);
    }

    public function edit_tourist($contest_id = '') {
        intercept('' == $contest_id, 'Invalid');

        $this->assign('contest_id', $contest_id);
        return view('edit_tourist');
    }

    /**
     * @param string $contest_id
     * @param string $user_id
     * @return \think\response\Json
     */
    public function add_tourist_json($contest_id = '', $user_id = '') {
        intercept_json('' == $contest_id, 'error');
        intercept_json('' == $user_id, 'error');
        $tourist = new ContestTouristModel();
        $tourist->contest_id = $contest_id;
        $tourist->user_id = $user_id;
        $tourist->save();
        return json([
            'code' => 0
        ]);
    }


    /**
     * @param string $contest_id
     * @param string $user_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function remove_tourist_json($contest_id = '', $user_id = '') {
        intercept_json('' == $contest_id, 'error');
        intercept_json('' == $user_id, 'error');
        $tourists = ContestTouristModel::all(['contest_id' => $contest_id, 'user_id' => $user_id]);
        foreach ($tourists as $tourist) {
            $tourist->delete();
        }
        return json([
            'code' => 0
        ]);
    }
}