<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/8
 * Time: 14:11
 */

namespace app\contests\controller;


use app\api\model\ContestEnrollModel;
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

	public function index($keyword = '')
	{
        $like_str = '%%';
	    if ('' != $keyword) {
	        $l = explode(' ', $keyword);
            $like_str = '%';
	        foreach ($l as $item) {
	            $like_str .= "{$item}%";
            }
        }


		$order_str = "start_time < '".date('Y-m-d H:i:s')."',end_time > '".date('Y-m-d H:i:s')."' desc, start_time desc";
		$contests = ContestModel::where('type', 0)
			->where('defunct','N')
            ->where('title', 'like', $like_str)
			->orderRaw($order_str)
			->paginate(10);
		if ('' != $keyword) $contests->appends('keyword', $keyword);

		foreach($contests as $contest) {
		    $contest->is_enroll = false;
		    if ($contest->is_need_enroll && $this->loginuser) {
		        $contest_enroll = ContestEnrollModel::get(['user_id' => $this->loginuser->user_id, 'contest_id' => $contest->contest_id]);
		        if ($contest_enroll) {
		            $contest->is_enroll = true;
                }
            }
        }

		$this->assign('title_val', $this->lang['contest']);
		$this->assign('contests', $contests);
		return view( $this->theme_root . '/contests', ['keyword' => htmlspecialchars($keyword), 'target' => '/contests']);
	}
}