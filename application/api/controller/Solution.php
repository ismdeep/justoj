<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/9
 * Time: 15:43
 */

namespace app\api\controller;


use app\api\model\CompileInfoModel;
use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\ApiBaseController;
use think\Db;

class Solution extends ApiBaseController
{
    /**
     * 提交题目代码
     * @param $problem_id
     * @param $language
     * @param $code
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
	public function submit_problem_code($problem_id='', $language='', $code='')
	{

	    $this->need_login('json');
	    intercept_json('' == $problem_id, 'problem_id不可为空');
        intercept_json('' == $language, 'language不可为空');
        intercept_json('' == $code, 'code不可为空');
        intercept_json(null == (new ProblemModel())->where('problem_id', $problem_id)->find(), '题目不存在');
        intercept_json(null != (new SolutionModel())->where(
            [
                'user_id' => $this->loginuser->user_id,
                'in_date' => ['>', date('Y-m-d H:i:s', time() - 3)]
            ]
            )->find(), '提交过于频繁');


		$solution = new SolutionModel();
		$solution->result = 14;
		$solution->problem_id = $problem_id;
		$solution->user_id = $this->loginuser->user_id;
		$solution->in_date = date('Y-m-d H:i:s', time());
		$solution->code_length = strlen($code);
		$solution->language = $language;
		$solution->ip = '';
		$solution->save();

		$source_code = new SourceCodeModel();
		$source_code->solution_id = $solution->solution_id;
		$source_code->source = $code;
		$source_code->save();

		$solution->result = 0;
		$solution->save();
		$solution->fk();
		$solution->result_text = $this->lang[$solution->result_code];

		// 带上源码一起返回
		$solution->source_code = SourceCodeModel::get(['solution_id' => $solution->solution_id]);
		$solution->source_code->source = str_replace('<', '&lt;', $solution->source_code->source);
		$solution->source_code->source = str_replace('>', '&gt;', $solution->source_code->source);

		return json(['status' => 'success', 'data' => $solution]);
	}

    /**
     * 比赛中提交代码
     * @param $contest_id
     * @param $problem_num
     * @param $language
     * @param $code
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
	public function submit_contest_problem_code($contest_id = '', $problem_num = '', $language = '', $code = '')
	{

        $this->need_login('json');
        intercept_json('' == $contest_id, 'contest_id不可为空');
        intercept_json(null == (new ContestModel())->where('contest_id', $contest_id)->find(), '比赛不存在');
        intercept_json('' == $problem_num, 'problem_num不可为空');
        intercept_json('' == $language, 'language不可为空');
        intercept_json('' == $code, 'code不可为空');
        $contest_problem = ContestProblemModel::get(['contest_id' => $contest_id, 'num' => $problem_num]);
        intercept_json(null == $contest_problem, '题目不存在');
        intercept_json(null == (new ProblemModel())->where('problem_id', $contest_problem->problem_id)->find(), '题目不存在');
        intercept_json(null != (new SolutionModel())->where(
                [
                    'user_id' => $this->loginuser->user_id,
                    'in_date' => ['>', date('Y-m-d H:i:s', time() - 3)]
                ]
            )->find(), '提交过于频繁');

        $solution = new SolutionModel();
		$solution->result = 14;
		$solution->contest_id = $contest_id;
		$solution->problem_id = $contest_problem->problem_id;
		$solution->user_id = $this->loginuser->user_id;
		$solution->in_date = date('Y-m-d H:i:s');
		$solution->code_length = strlen($code);
		$solution->language = $language;
		$solution->ip = '';
		$solution->save();

		$source_code = new SourceCodeModel();
		$source_code->solution_id = $solution->solution_id;
		$source_code->source = $code;
		$source_code->save();

		$solution->result = 0;
		$solution->save();
		$solution->fk();
		$solution->result_text = $this->lang[$solution->result_code];
		return json(['status' => 'success', 'data' => $solution]);
	}

	/**
	 * solution状态
	 * @param $solution_id
	 * @return \think\response\Json
	 * @throws \think\exception\DbException
	 */
	public function status($solution_id)
	{
		$solution = SolutionModel::get(['solution_id' => $solution_id]);

		// 检查solution是否存在
		if (!$solution) return json(['status' => 'error', 'msg' => $this->lang['solution_not_exists']]);

		// fk额外东西
		$solution->fk();
		$solution->result_text = $this->lang[$solution->result_code];
		$solution->compile_info = CompileInfoModel::get(['solution_id' => $solution->solution_id]);

		return json(['status' => 'success', 'data' => $solution]);
	}

	public function details($id)
	{
		$solution = SolutionModel::get(['solution_id' => $id]);

		// 检查solution是否存在
		if (!$solution) return json(['status' => 'error', 'msg' => $this->lang['solution_not_exists']]);

		// 检查是否有访问权限
		if (!($this->is_administrator || ($this->loginuser && $solution->user_id == $this->loginuser->user_id))) {
			return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
		}

		$solution->fk();
		$solution->result_text = $this->lang[$solution->result_code];
		$solution->source_code = SourceCodeModel::get(['solution_id' => $solution->solution_id]);
		$solution->source_code->source = str_replace('<', '&lt;', $solution->source_code->source);
		$solution->source_code->source = str_replace('>', '&gt;', $solution->source_code->source);

		// 如果编译错误则带上编译错误信息
		if (11 == $solution->result) {
			$solution->compile_info = CompileInfoModel::get(['solution_id' => $id]);
			if (!$solution->compile_info) {
				$solution_new = SolutionModel::get(['solution_id' => $id]);
				$solution_new->result = 1;
				$solution_new->save();
			}
			while (!$solution->compile_info) $solution->compile_info = CompileInfoModel::get(['solution_id' => $id]);
		}

		return json(['status' => 'success', 'data' => $solution]);
	}

	/**
	 * 重判题目
	 */
	public function rejudge_problem($problem_id)
	{
		if (!$this->is_root) return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);

		$problem = ProblemModel::get(['problem_id' => $problem_id]);
		if (!$problem) return json(['status' => 'error', 'msg' => $this->lang['no_such_problem']]);
		// 查看是否有题目还在判题中
		if (Db::query("select count(solution_id) as cnt from solution where problem_id=".$problem_id." and result=1")[0]['cnt'] > 0) {
			return json(['status' => 'error', 'msg' => 'Problem is still rejudging.']);
		}

		Db::query("update solution set result=1 where problem_id=".$problem_id);
		return json(['status' => 'success', 'msg' => 'rejudge success']);
	}
}