<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/8
 * Time: 14:11
 */

namespace app\contests\controller;


use app\api\model\ContestModel;
use app\extra\controller\UserBaseController;
use think\Db;
use think\Request;

class Index extends UserBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'contests');
	}

	public function index()
	{
		$order_str = "start_time < '".date('Y-m-d H:i:s')."',end_time > '".date('Y-m-d H:i:s')."' desc, start_time desc";
		$contests = ContestModel::where('type', 0)
			->where('defunct','N')
			->orderRaw($order_str)
			->paginate(10);
		$this->assign('title_val', $this->lang['contest']);
		$this->assign('contests', $contests);
		return view();
	}
}