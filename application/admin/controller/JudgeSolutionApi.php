<?php


namespace app\admin\controller;


use app\api\model\CompileInfoModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\AdminBaseController;

class JudgeSolutionApi extends AdminBaseController
{
    /**
     * 获取等待判题的Solution
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/get_pending?max_running=1&oj_lang_set=1,2,3
     *
     * @param int $max_running
     * @param string $oj_lang_set
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_pending($max_running = 1, $oj_lang_set = '')
    {
        $max_running = intval($max_running);
        $oj_lang_list = explode(',', $oj_lang_set);
        $solutions = (new SolutionModel())
            ->where('result', 'in', [0, 1])
            ->where('language', 'in', $oj_lang_list)
            ->limit($max_running)
            ->select();
        foreach ($solutions as $solution) {
            echo $solution->solution_id . "\n";
        }
    }

    /**
     * 提交判题结果
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/update_solution?sid=622923&result=4&time=3&memory=1668
     *
     * @param string $sid
     * @param string $result
     * @param string $time
     * @param string $memory
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_solution($sid = '', $result = '', $time = '', $memory = '')
    {
        $solution_id = intval($sid);
        $result = intval($result);
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, "SOLUTION NOT FOUND. [solution_id:$solution_id]");
        $solution->result = $result;
        $solution->memory = $memory;
        $solution->time = $time;
        $solution->save();
    }

    /**
     * 临时改变Solution结果（比如改为正在编译等）
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/checkout?sid=622923&result=1
     *
     * @param string $sid
     * @param string $result
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
        echo "1\n";
    }


    /**
     * 获取Solution的源代码
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
     * 获取Solution的基本信息
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
     * 添加Solution的Compile Error
     *
     * http://justoj-web.ismdeep.com/admin/judge_solution_api/add_ce_info?sid=2394&ceinfo=CCC
     *
     * @param string $sid
     * @param string $ceinfo
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
    }
}
