<?php


namespace app\api\controller;


use app\api\model\CompileInfoModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\UserModel;
use app\extra\controller\ApiBaseController;
use think\Request;

class JudgeApi extends ApiBaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $secure_code = $request->param('secure_code');
        intercept(config('secure_code') != $secure_code, '0');
    }

    /**
     * Judge secure_code is valid
     *
     * http://oj.jxust.edu.cn/api/judge_api/check_secure_code
     *
     * @return string
     */
    public function check_secure_code()
    {
        return '1';
    }

    /**
     * Get pending solution ids
     *
     * http://oj.jxust.edu.cn/api/judge_api/get_pending?max_running=1&oj_lang_set=1,2,3
     *
     * @param int $query_size
     * @param string $oj_lang_set
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_pending($query_size = 1, $oj_lang_set = '')
    {
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
        foreach ($solutions as $solution) {
            echo $solution->solution_id . "\n";
        }
    }

    /**
     * Update solution running result temporary(e.g. Compiling)
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/checkout?sid=622923&result=1
     *
     * @param string $sid
     * @param string $result
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkout($sid = '', $result = '')
    {
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
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/update_solution?sid=622923&result=4&time=3&memory=1668
     *
     * @param string $sid
     * @param string $result
     * @param string $time
     * @param string $memory
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_solution($sid = '', $result = '', $time = '', $memory = '')
    {
        $solution_id = intval($sid);
        $result = intval($result);
        if ($result == 5) {
            $result = 4;
        }
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $solution->result = $result;
        $solution->memory = $memory;
        $solution->time = $time;
        $solution->save();
        return '1';
    }


    /**
     * Get solution source code
     *
     * @param string $sid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_solution($sid = '')
    {
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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_solution_info($sid = '')
    {
        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        echo $solution->problem_id . "\n";
        echo $solution->user_id . "\n";
        echo $solution->language. "\n";
        echo intval( $solution->contest_id ) . "\n";
    }


    /**
     * Add Compile Error to Solution
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/add_ce_info?sid=2394&ceinfo=CCC
     *
     * @param string $sid
     * @param string $ceinfo
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add_ce_info($sid = '', $ceinfo = '')
    {
        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");

        $compile_info = new CompileInfoModel();
        $compile_info->solution_id = $solution_id;
        $compile_info->error       = $ceinfo;
        $compile_info->save();
        return "1";
    }

    /**
     * Update problem submission count and solved count
     *
     * @param string $problem_id
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_problem($problem_id = '')
    {
        intercept('' == $problem_id, '0');
        $problem_id = intval($problem_id);

        $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
        intercept(null == $problem, '0');

        $problem->accepted = (new SolutionModel())
            ->where('problem_id', $problem_id)
            ->where('result', 4)
            ->where('contest_id', null)
            ->count();
        $problem->submit = (new SolutionModel())
            ->where('problem_id', $problem_id)
            ->where('contest_id', null)
            ->count();
        $problem->save();

        return '1';
    }

    /**
     * Update user solved count and submit count
     *
     * @param string $user_id
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_user($user_id = '')
    {
        intercept('' == $user_id, '0');
        $user = (new UserModel())->where('user_id', $user_id)->find();
        intercept(null == $user, '0');

        $user->submit = (new SolutionModel())
            ->where('user_id', $user_id)
            ->where('contest_id', null)
            ->count();
        $user->solved = (new SolutionModel())
            ->where('user_id', $user_id)
            ->where('result', 4)
            ->where('contest_id', null)
            ->distinct('problem_id')
            ->count();
        $user->save();

        return '1';
    }
}
