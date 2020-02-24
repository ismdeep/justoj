<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/29
 * Time: 17:30
 */

namespace app\group\controller;


use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use app\extra\controller\UserBaseController;

class Join extends UserBaseController
{
	public function index($id)
	{
		// 判断是否登录，如果没有登录则直接跳转至登录页面。
		if (!$this->loginuser) $this->redirect('/login?redirect=%2Fgroup%2FJoin?id='.$id);

		$group = GroupModel::get(['id' => $id]);
		if (!$group) return view('not_found_group');

		// 判断当前用户是否有访问班级权限
		// 判断当前用户是否为此班级管理员
		$is_group_manager = false;
		if ($this->loginuser && $group->owner_id == $this->loginuser->user_id) $is_group_manager = true;
		$this->assign('is_group_manager', $is_group_manager);

		// 判断当前用户是否有访问权限
		$have_permission = false;
		// 判断当前用户与班级是否有group_join,并且status=1
		if ($is_group_manager) $have_permission = true;

		$group_join = GroupJoinModel::get(['user_id' => $this->loginuser->user_id, 'group_id' => $id]);
		if ($group_join && $group_join->status == 1) $have_permission = true;

		if ($have_permission) $this->redirect('/group?id='.$id);

		$this->assign('group', $group);
		// 如果此班级为public，则询问学生是否加入。(type: 0public 1private)
		// 如果此班级为private但是没有密码，则询问学生是否加入，点击加入后告知学生需要等待管理员审核。
		// 如果此班级为private且有密码，则询问学生加入密码，密码正确则直接加入此班级。
		return view($this->theme_root . '/group-join');
	}
}