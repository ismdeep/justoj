<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/30
 * Time: 3:16 PM
 */

namespace app\groups\controller;


use app\api\model\GroupModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Mycreation extends UserBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'groups');
	}

    /**
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
	public function index()
	{
		if (!$this->is_administrator) $this->redirect('/login?redirect=%2Fgroups%2FMycreation');
		$groups = (new GroupModel())
            ->where([
                'owner_id' => $this->loginuser->user_id,
                'deleted' => 0
            ])
            ->order('id', 'asc')
            ->paginate(10);
		$this->assign('groups', $groups);
		return view($this->theme_root . '/groups-my-creation');
	}
}
