<?php


namespace app\api\controller;


use app\api\model\CompileInfoModel;
use app\api\model\PrivilegeModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\UserModel;
use app\extra\controller\ApiBaseController;
use app\extra\util\PasswordUtil;
use think\Session;

class JudgeApi extends ApiBaseController
{
    /**
     * Judge secure_code is valid
     *
     * http://oj.jxust.edu.cn/api/judge_api/check_secure_code
     *
     * @param string $secure_code
     * @return string
     */
    public function check_secure_code($secure_code = '')
    {
        if (config('secure_code') != $secure_code) return "0";

        return "1";
    }

    /**
     * Get Pending Solution IDs
     *
     * http://oj.jxust.edu.cn/api/judge_api/get_pending?max_running=1&oj_lang_set=1,2,3
     *
     * @param int $query_size
     * @param string $secure_code
     * @param string $oj_lang_set
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_pending($query_size = 1, $secure_code = '', $oj_lang_set = '')
    {
        if (config('secure_code') != $secure_code) return "0";

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
     * 临时改变Solution结果（比如改为正在编译等）
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/checkout?sid=622923&result=1
     *
     * @param string $sid
     * @param string $secure_code
     * @param string $result
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkout($sid = '', $secure_code = '', $result = '')
    {
        if (config('secure_code') != $secure_code) return "0";

        $solution_id = intval($sid);
        $result = intval($result);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $solution->result = $result;
        $solution->save();
        echo "1\n";
    }


    /**
     * 提交判题结果
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/update_solution?sid=622923&result=4&time=3&memory=1668
     *
     * @param string $sid
     * @param string $secure_code
     * @param string $result
     * @param string $time
     * @param string $memory
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_solution($sid = '', $secure_code = '', $result = '', $time = '', $memory = '')
    {
        if (config('secure_code') != $secure_code) return "0";

        $solution_id = intval($sid);
        $result = intval($result);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $solution->result = $result;
        $solution->memory = $memory;
        $solution->time = $time;
        $solution->save();
        echo "1\n";
    }


    /**
     * 获取Solution的源代码
     *
     * @param string $sid
     * @param string $secure_code
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_solution($sid = '', $secure_code = '')
    {
        if (config('secure_code') != $secure_code) return "0";

        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $source_code = (new SourceCodeModel())->where('solution_id', $solution_id)
            ->find();
        intercept(null == $source_code, "SOLUTION SOURCE CODE NOT FOUND. [solution_id:$solution_id]");
        echo $source_code->source;
    }


    /**
     * 获取Solution的基本信息
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/get_solution_info?sid=2394
     *
     * @param string $sid
     * @param string $secure_code
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_solution_info($sid = '', $secure_code = '')
    {
        if (config('secure_code') != $secure_code) return "0";

        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        echo $solution->problem_id . "\n";
        echo $solution->user_id . "\n";
        echo $solution->language. "\n";
        echo intval( $solution->contest_id ) . "\n";
    }


    /**
     * 添加Solution的Compile Error
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/add_ce_info?sid=2394&ceinfo=CCC
     *
     * @param string $sid
     * @param string $ceinfo
     * @param string $secure_code
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add_ce_info($sid = '', $ceinfo = '', $secure_code = '')
    {
        if (config('secure_code') != $secure_code) return "0";

        $solution_id = intval($sid);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");

        $compile_info = new CompileInfoModel();
        $compile_info->solution_id = $solution_id;
        $compile_info->error       = $ceinfo;
        $compile_info->save();
        return "1";
    }

}
