<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/13
 * Time: 10:26 PM
 */

namespace app\group\controller;


use app\api\model\GroupAnnounceModel;
use app\extra\controller\GroupBaseController;
use think\Request;

class Notifications extends GroupBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'notifications');
	}

	/**
	 * 班级公告
	 */
	public function index()
	{
		// 获取当前班级之公告
		$notifications = GroupAnnounceModel::all(['group_id' => $this->group->id]);
		$this->assign('notifications', $notifications);
		return view();
	}
}