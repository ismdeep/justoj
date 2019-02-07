<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 20:49
 */

namespace app\problem\controller;


use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\extra\controller\UserBaseController;
use think\Controller;
use think\Db;

class Index extends UserBaseController
{
    public function index ($id)
    {
        $problem = ProblemModel::get(['problem_id' => $id]);
        if (!$problem){
        	// TODO 后期加入404 NOT FOUND页面
        	$this->redirect('/problems');
		}

		if ('Y' == $problem->defunct && !$this->is_administrator) {
        	// TODO 后期加入无访问权限操作
			$this->redirect('/problems');
		}
		// 如果当前用户登录了，判断AC状态
		$problem->ac = false;
		$problem->pending = false;
		if ($this->loginuser) {
			if (SolutionModel::
			where("contest_id", null)
				->where('user_id', $this->loginuser->user_id)
				->where('problem_id', $problem->problem_id)
				->where('result', 4)
				->find()) {
				$problem->ac = true;
			}else{
				if(SolutionModel::
				where("contest_id", null)
					->where('user_id', $this->loginuser->user_id)
					->where('problem_id', $problem->problem_id)
					->find()){
					$problem->pending = true;
				}
			}
		}

        $problem->fk();
        $this->assign('problem', $problem);
        $this->assign('allowed_langs', $this->allowed_langs());
        return view();
    }
}