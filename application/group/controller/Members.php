<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/13
 * Time: 10:37 PM
 */

namespace app\group\controller;


use app\api\model\GroupJoinModel;
use app\api\model\UserModel;
use app\extra\controller\GroupBaseController;
use think\Request;

class Members extends GroupBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'members');
	}

	/**
	 * 班级成员页面
	 */
	public function index()
	{
		$members = GroupJoinModel::all(['group_id' => $this->group->id]);
		foreach ($members as $member) {
			$member->realname = UserModel::get(['user_id' => $member->user_id])->realname;
		}
		$this->assign('members', $members);
		return view($this->theme_root . '/group-members');
	}
}
