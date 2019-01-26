<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/9/19
 * Time: 10:06 PM
 */

namespace app\admin\controller;


use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\ProblemModel;
use app\extra\controller\AdminBaseController;

class Contest extends AdminBaseController
{
    public function contest_list()
    {
        return view();
    }

    /**
     * @param int $page
     * @param int $limit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function contest_list_json($page = 1, $limit = 10)
    {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $where = ['type' => 0];
        $contests = (new ContestModel())->where($where)->order('contest_id', 'desc')->limit(($page - 1) * $limit, $limit)->select();
        $count = (new ContestModel())->where($where)->count();
        return json([
            'code' => 0,
            'count' => $count,
            'data' => $contests
        ]);
    }

    public function change_defunct_json($contest_id='', $defunct = '')
    {
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

    public function add()
    {
        $contest = new ContestModel();
        $contest->contest_id = '';
        $contest->title = '';
        $contest->description = '';
        $contest->start_time = '';
        $contest->end_time = '';
        $contest->problem_ids = '';
        $contest->langmask = '*';

        $allowed_langs_all = $this->allowed_langs();
        for ($i = 0; $i < sizeof($allowed_langs_all); ++$i) {
            $allowed_langs_all[$i]['allowed'] = true;
        }
        $this->assign('allowed_langs_all', $allowed_langs_all);

        $this->assign('contest', $contest);
        return view('edit');
    }

    /**
     * @param string $contest_id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($contest_id = '')
    {
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
     * @return \think\response\View
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
        $password = ''
    )
    {

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
                    'msg' => 'Problem not exists. id: '. $pid
                ]);
            }
            $problems[] = $problem;
        }

        // 判断contest_id是否存在
        $contest = null;
        if ('' == $contest_id) {
            $contest = new ContestModel(); // 创建比赛
            $contest->defunct = 'N';
            $contest->type = 1;
        }else{
            // 判断contest实体是否存在
            $contest = ContestModel::get(['contest_id' => $contest_id]);
            if (null == $contest) {
                return json([ 'status' => 'error', 'msg' => $this->lang['contest_not_exists']]);
            }

            // 删除contest_problem里面的记录
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
        }else{
            $contest->password = '';
        }
        /**
         * 写入编程语言信息
         */
        if ('*' == $langmask_flag) {
            $contest->langmask = '*';
        }else{
            $contest->langmask = $allowed_langs;
        }
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

}