<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/13
 * Time: 10:41 PM
 */

namespace app\group\controller;


use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\GroupTaskModel;
use app\extra\controller\GroupBaseController;
use think\Db;
use think\Request;

class Tasks extends GroupBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'tasks');
	}

	/**
	 * 作业页面
	 */
	public function index()
	{
		$tasks = GroupTaskModel::all(['group_id' => $this->group->id]);
//		return json(['data' => $tasks]);
		foreach ($tasks as $task) {
			$task->contest = ContestModel::get(['contest_id' => $task->contest_id]);
			// 获取这个比赛题目数量
			$task->contest->problem_cnt = Db::query("select count(problem_id) as cnt from contest_problem where contest_id=".$task->contest_id)[0]['cnt'];
			// 获得登录用户A题数量
			$task->contest->loginuser_ac_cnt = Db::query("select count(DISTINCT problem_id) as cnt from solution where contest_id=".$task->contest_id." and user_id='".$this->loginuser->user_id."' and result=4")[0]['cnt'];
		}
		$this->assign('tasks', $tasks);
		return view();
	}

	/**
	 * 下载班级作业excel表格
	 */
	public function download_excel($contest_id)
	{
	}
}
