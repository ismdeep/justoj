<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 13:34
 */

namespace app\extra\controller;


use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use think\Request;

class GroupBaseController extends UserBaseController
{
	public $group;
	public $is_group_manager;
	public $have_permission;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        // 判断是否登录，如果没有登录直接跳转到登录页面
		if (!$this->loginuser) $this->redirect('/login?redirect='.urlencode($_SERVER['REQUEST_URI']));

        // 获取group信息
		$this->group = GroupModel::get(['id' => $request->get('id')]);
		intercept($this->group == null, 'NOT EXISTS');
		intercept($this->group->deleted == 1, 'DELETED');
		$this->assign('group', $this->group);

		// 判断当前用户是否为此班级管理员
		$this->is_group_manager = false;
		if ($this->loginuser && $this->group->ownner_id == $this->loginuser->user_id) $this->is_group_manager = true;
		$this->assign('is_group_manager', $this->is_group_manager);

		// 判断当前用户是否有访问权限
		$this->have_permission = false;
		// 判断当前用户与班级是否有group_join,并且status=1
		if ($this->is_group_manager) $this->have_permission = true;

		$group_join = GroupJoinModel::get(['user_id' => $this->loginuser->user_id, 'group_id' => $request->get('id')]);
		if ($group_join && $group_join->status == 1) $this->have_permission = true;

		if (!$this->have_permission) $this->redirect('/group/join?id='.$request->get('id'));

    }
}
