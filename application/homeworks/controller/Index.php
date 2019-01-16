<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/9
 * Time: 8:13 PM
 */

namespace app\homeworks\controller;


use app\api\model\ContestModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Index extends UserBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'homeworks');
	}

	public function index()
	{
		$order_str = "start_time < '".date('Y-m-d H:i:s')."',end_time > '".date('Y-m-d H:i:s')."' desc, start_time desc";
		$contests = ContestModel::where('type', 1)
			->where('defunct','N')
			->orderRaw($order_str)
			->paginate(10);
		$this->assign('contests', $contests);
		$this->assign('title_val', $this->lang['homework']);
		return view('contests@index/index');
	}
}