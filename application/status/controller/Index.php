<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 12:36
 */

namespace app\status\controller;


use app\api\model\SolutionModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Index extends UserBaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->assign('nav', 'status');
    }

    /**
     * @param string $run_id
     * @param string $username
     * @param string $problem_id
     * @param string $result
     * @param string $language
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function index ($run_id='', $username='', $problem_id='', $result='', $language='')
    {
    	$this->assign('run_id', $run_id);
    	$this->assign('username', $username);
    	$this->assign('problem_id', $problem_id);
    	$this->assign('result', $result);
    	$this->assign('language', $language);
    	$this->assign('allowed_langs', $this->allowed_langs());
    	if ('' != $run_id) {
    		$solutions = (new SolutionModel)->where('solution_id', $run_id)->paginate(10);
    		$solutions->appends('run_id', $run_id);
			$this->assign('solutions', $solutions);
			return view();
		}

		$where = (new SolutionModel());
    	$where = $where->where(['contest_id' => null]);

		if ('' != $username){
			$where = $where->where(['user_id' => $username]);
		}
		if ('' != $problem_id){
		    $where = $where->where(['problem_id' => $problem_id]);
		}
		if ('' != $result){
		    $where = $where->where(['result' => $result]);
		}
		if ('' != $language){
		    $where = $where->where(['language' => $language]);
		}

		$solutions = $where->order('solution_id', 'desc')->paginate(10);
		$solutions->appends('run_id', '');
		$solutions->appends('username', $username);
		$solutions->appends('problem_id', $problem_id);
		$solutions->appends('result', $result);
		$solutions->appends('language', $language);
        $this->assign('solutions', $solutions);
        return view();
    }
}