<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/9
 * Time: 11:46 PM
 */

namespace app\contest\controller;


use app\api\model\GroupJoinModel;
use app\api\model\PrivilegeModel;
use app\extra\controller\ContestBaseController;
use think\Request;

class Statistics extends ContestBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'statistics');
		if (!$this->permitted) {
			$this->redirect('/contest?id='.$this->contest->contest_id);
		}
	}

	public function index()
	{
		return view();
	}
}