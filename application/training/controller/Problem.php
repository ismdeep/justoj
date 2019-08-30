<?php


namespace app\training\controller;


use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\TrainingProblemModel;
use app\training\common\TrainingBaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\response\Json;
use think\response\View;

/**
 * 训练场题目
 * Class Problem
 * @package app\training\controller
 */
class Problem extends TrainingBaseController
{
    /**
     * 训练场题目页面
     * @param string $id
     * @return View
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function index($id = '')
    {
        intercept('' == $id, '参数错误');
        $training_problem = (new TrainingProblemModel())->where('id', $id)->find();
        intercept(null == $training_problem, '问题不存在');
        $problem = (new ProblemModel())->where('problem_id', $training_problem->problem_id)->find();

        /* 处理 AC 和 PENDING 状态 >>>> */
        $problem->ac = false;
        $problem->pending = false;
        if ( (new SolutionModel())->where([
                'problem_id' => $training_problem->problem_id,
                'contest_id' => 1,
                'result' => 4,
                'user_id' => $this->loginuser->user_id,
            ])->find() != null ) {
            $problem->ac = true;
        }
        if ($problem->ac == false && (new SolutionModel())->where([
                'problem_id' => $training_problem->problem_id,
                'contest_id' => 1,
                'user_id' => $this->loginuser->user_id,
            ])->find() != null) {
            $problem->pending = true;
        }
        /* <<<< 处理 AC 和 PENDING 状态 */

        /* 获取提交数量 */
        $problem->submit = (new SolutionModel())->where(['problem_id' => $problem->problem_id, 'contest_id' => 1])->count();

        /* 获取 AC 数量 */
        $problem->accepted = (new SolutionModel())->where(['problem_id' => $problem->problem_id, 'contest_id' => 1, 'result' => 4])->count();

        return view($this->theme_root . '/training/problem', [
            'training_problem' => $training_problem,
            'problem' => $problem,
            'allowed_langs' => $this->allowed_langs()
        ]);
    }

    /**
     * 提交代码
     * @param string $id
     * @param string $code
     * @param string $language
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function submit_json($id = '', $code = '', $language = '')
    {
        intercept_json('' == $id, 'id不可为空');
        intercept_json('' == $code, '提交代码不可为空');
        intercept_json('' == $language, '请选择语言');

        $training_problem = (new TrainingProblemModel())->where('id', $id)->find();
        intercept_json(null == $training_problem, '题目不存在');

        /* 判断是否有未完成测评的提交 >>>> */
        $unsolved_solution = (new SolutionModel())->where([
            'problem_id' => $training_problem->problem_id,
            'user_id' => $this->loginuser->user_id,
            'contest_id' => 1
        ])->whereIn('result', '0,1,2,3')->find();
        intercept_json(null != $unsolved_solution, '请等待上次提交判题结束后再提交。');
        /* <<<< 判断是否有未完成测评的提交 */

        /* 判断是否已经AC了 */
        $aced = (new SolutionModel())->where([
            'problem_id' => $training_problem->problem_id,
            'user_id' => $this->loginuser->user_id,
            'contest_id' => 1,
            'result' => 4
        ])->find() != null;


        /* 将提交的代码写入数据库 >>>> */
        $solution = new SolutionModel();
        $solution->result = 14;
        $solution->problem_id = $training_problem->problem_id;
        $solution->contest_id = 1;
        $solution->user_id = $this->loginuser->user_id;
        $solution->in_date = date('Y-m-d H:i:s', time());
        $solution->code_length = strlen($code);
        $solution->language = $language;
        $solution->ip = '';
        $solution->first_training = $aced == true ? 0 : 1;
        $solution->save();

        $source_code = new SourceCodeModel();
        $source_code->solution_id = $solution->solution_id;
        $source_code->source = $code;
        $source_code->save();

        $solution->result = 0;
        $solution->save();
        $solution->fk();
        $solution->result_text = $this->lang[$solution->result_code];
        /* <<<< 将提交的代码写入数据库 */

        // 带上源码一起返回
        $solution->source_code = SourceCodeModel::get(['solution_id' => $solution->solution_id]);
        $solution->source_code->source = str_replace('<', '&lt;', $solution->source_code->source);
        $solution->source_code->source = str_replace('>', '&gt;', $solution->source_code->source);

        return json(['status' => 'success', 'data' => $solution]);
    }
}