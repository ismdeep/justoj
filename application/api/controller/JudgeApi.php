<?php


namespace app\api\controller;


use app\api\model\CompileInfoModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\UserModel;
use app\extra\controller\ApiBaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;

class JudgeApi extends ApiBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $secure_code = $request->param('secure_code');
        intercept(config('secure_code') != $secure_code, '0');
    }

    /**
     * Judge secure_code is valid
     *
     * /api/judge_api/check_secure_code
     *
     * @return string
     */
    public function check_secure_code() {
        return '1';
    }

    /**
     * Get pending solution ids
     *
     * /api/judge_api/get_pending?max_running=1&oj_lang_set=1,2,3
     *
     * @param int $query_size
     * @param string $oj_lang_set
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function get_pending($query_size = 1, $oj_lang_set = '') {
        $query_size = intval($query_size);
        $oj_lang_list = explode(',', $oj_lang_set);
        $solutions = (new SolutionModel())
            ->where('result', 'in', [0, 1])
            ->where('language', 'in', $oj_lang_list)
            ->order('result', 'asc')
            ->order('solution_id', 'asc')
            ->limit($query_size)
            ->select();
        echo "solution_ids\n";

        $solution_ids = [];
        foreach ($solutions as $solution) {
            echo $solution->solution_id . "\n";
            $solution_ids []= $solution->solution_id;
        }

        (new UserModel())->where(['id' => $solution_ids])->update(['result', 2]);
    }

    /**
     * Update solution running result temporary(e.g. Compiling)
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/checkout?sid=622923&result=1
     *
     * @param string $sid
     * @param string $result
     * @return string
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function checkout($sid = '', $result = '') {
        $solution_id = intval($sid);
        $result = intval($result);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $solution->result = $result;
        $solution->save();
        return '1';
    }


    /**
     * Update solution running result
     *
     * http://justoj.ismdeep.com/api/judge_api/update_solution?sid=622923&result=4&time=3&memory=1668
     *
     * @param string $sid
     * @param string $result
     * @param string $time
     * @param string $memory
     * @return string
     * @throws \think\Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function update_solution($sid = '', $result = '', $time = '', $memory = '') {
        $solution_id = intval($sid);
        $result = intval($result);
        if ($result == 5) {
            $result = 4;
        }
        /* @var $solution SolutionModel */
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $solution->result = $result;
        $solution->memory = $memory;
        $solution->time = $time;
        $solution->save();

        /* update user info */
        UserModel::update_ac_cnt($solution->user_id);

        /* update problem info */
        ProblemModel::update_ac_cnt($solution->problem_id);

        return '1';
    }


    /**
     * Get solution source code
     *
     * @param string $sid
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function get_solution($sid = '') {
        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $source_code = (new SourceCodeModel())->where('solution_id', $solution_id)
            ->find();
        intercept(null == $source_code, "SOLUTION SOURCE CODE NOT FOUND. [solution_id:$solution_id]");
        echo $source_code->source;
    }


    /**
     * Get solution info
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/get_solution_info?sid=2394
     *
     * @param string $sid
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function get_solution_info($sid = '') {
        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        echo $solution->problem_id . "\n";
        echo $solution->user_id . "\n";
        echo $solution->language . "\n";
        echo intval($solution->contest_id) . "\n";
    }


    /**
     * Add Compile Error to Solution
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/add_ce_info?sid=2394&ceinfo=CCC
     *
     * @param string $sid
     * @param string $ceinfo
     * @return string
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function add_ce_info($sid = '', $ceinfo = '') {
        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");

        $compile_info = new CompileInfoModel();
        $compile_info->solution_id = $solution_id;
        $compile_info->error = $ceinfo;
        $compile_info->save();
        return "1";
    }

    public function get_problem_info($pid = '') {
        $pid = intval($pid);
        $problem = (new ProblemModel())->where('problem_id', $pid)->find();
        intercept(null == $problem, "PROBLEM NOT FOUND. [pid:$pid]");
        echo $problem->time_limit . "\n";
        echo $problem->memory_limit . "\n";
        echo $problem->spj . "\n";
    }
}
