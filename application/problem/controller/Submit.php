<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 11:37 PM
 */

namespace app\problem\controller;


use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\UserBaseController;

class Submit extends UserBaseController
{
	public function index ($id,$language,$source)
	{
		$solution = new SolutionModel();
		$solution->problem_id = $id;
		$solution->user_id = $this->loginuser->user_id;
		$solution->result = 0;
		$solution->language = $language;
		$solution->in_date = date('Y-m-d H:i:s');
		$solution->code_length = strlen($source);
		$solution->save();

		$source_code = new SourceCodeModel();
		$source_code->solution_id = $solution->solution_id;
		$source_code->source = $source;
		$source_code->save();
		return $solution->solution_id;
	}
}