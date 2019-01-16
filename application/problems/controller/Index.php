<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 20:31
 */

namespace app\problems\controller;


use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\extra\controller\UserBaseController;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

class Index extends UserBaseController
{
    public $max_p;
    public $page_cnt;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->assign('nav', 'problem');
        // 获取总页数
        $this->max_p = (new ProblemModel)->order('problem_id','desc')->limit(1)->select()[0];
        $this->page_cnt = ceil(($this->max_p->problem_id - 1000 + 1) / 100.0);
        $this->assign('page_cnt', $this->page_cnt);
    }

    public function index($keyword='', $page = 1)
    {
        $page = intval($page);
        $this->assign('page', $page);
        $this->assign('keyword', $keyword);

        if ('' != $keyword) {
            $problems = ProblemModel::where('title','like','%'.$keyword.'%')->whereOr('source','like','%'.$keyword.'%')->order('problem_id', 'asc')->paginate(100);
            foreach ($problems as $problem) {
				$problem->solve_status = 0; // 无状态
				if ($this->loginuser){
					// 判断是否有提交
					if (SolutionModel::get(['user_id' => $this->loginuser->user_id,'problem_id' => $problem->problem_id,'contest_id' => null])){
						$problem->solve_status = 1; // 有提交
					}
					// 判断是否有AC题目
					if (SolutionModel::get(['user_id' => $this->loginuser->user_id,'problem_id' => $problem->problem_id,'contest_id' => null,'result' => 4])){
						$problem->solve_status = 2; // 已通过
					}
				}
			}
            $problems->appends('keyword', $keyword);
            $this->assign('problems', $problems);
            return view();
        }

        $problems = (new ProblemModel)
            ->where('problem_id', '>=', ($page + 9) * 100)
            ->where('problem_id', '<', ($page + 10) * 100)
            ->select();

        foreach ($problems as $problem){
            $problem->fk();

            // 获取当前登录用户的解题情况
			$problem->solve_status = 0; // 无状态
			if ($this->loginuser){
				// 判断是否有提交
				if (SolutionModel::get(['user_id' => $this->loginuser->user_id,'problem_id' => $problem->problem_id,'contest_id' => null])){
					$problem->solve_status = 1; // 有提交
				}
				// 判断是否有AC题目
				if (SolutionModel::get(['user_id' => $this->loginuser->user_id,'problem_id' => $problem->problem_id,'contest_id' => null,'result' => 4])){
					$problem->solve_status = 2; // 已通过
				}
			}
		}
        $this->assign('problems', $problems);
        return view();
    }
}