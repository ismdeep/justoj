<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 13:33
 */

namespace app\groups\controller;


use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Index extends UserBaseController
{

	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'groups');
	}

    /**
     * 所有group分页
     * @param string $keyword
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function index($keyword = '')
    {
        $where = new GroupModel();
        if ('' != $keyword) {
            $where = $where->where('name', 'like', "%{$keyword}%");
        }
        $groups = $where->order('id', 'desc')->paginate(10);
        if ($this->loginuser) {
			foreach ($groups as $group) $group->loginuser_group_join = GroupJoinModel::get(['user_id' => $this->loginuser->user_id, 'group_id' => $group->id]);
		}else{
        	foreach ($groups as $group) $group->loginuser_group_join = null;
		}
        $groups->appends('keyword', $keyword);
        $this->assign('keyword', htmlspecialchars($keyword));
        $this->assign('groups', $groups);
        return view($this->theme_root . '/groups');
    }
}