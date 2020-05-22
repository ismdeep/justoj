<?php


namespace app\home\controller;


use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Group extends UserBaseController {

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'groups');
    }

    /**
     * 所有group分页
     *
     * @param string $keyword 关键字搜索
     * @param string $filter 筛选，空为所有班级，1我创建的班级，2我加入的班级
     *
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function get_group_list($keyword = '', $filter = '') {
        $groups = new GroupModel();
        $groups = $groups->where('deleted', 0);

        if ($keyword) {
            $groups = $groups->where('name', 'like', "%{$keyword}%");
        }

        if (in_array($filter, [1,2]) && !$this->loginuser) {
            $this->redirect('/login?redirect=' . urlencode('/groups'));
        }

        switch ($filter) {
            case 1:
                $groups = $groups->where(['owner_id' => $this->loginuser->user_id]);
                break;
            case 2:
                $groups = $groups->where('id', 'in', function($query){
                    $query->table('group_join')
                        ->where([
                            'user_id' => $this->loginuser->user_id,
                            'deleted' => 0
                        ])->field('group_id');
                });
                break;
        }

        $groups = $groups->order('id', 'desc')->paginate(10);
        if ($this->loginuser) {
            foreach ($groups as $group) $group->loginuser_group_join = GroupJoinModel::get(['user_id' => $this->loginuser->user_id, 'group_id' => $group->id]);
        } else {
            foreach ($groups as $group) $group->loginuser_group_join = null;
        }

        $groups->appends(['keyword' => $keyword, 'filter' => $filter]);
        $this->assign('keyword', htmlspecialchars($keyword));
        $this->assign('groups', $groups);
        $this->assign('filter', $filter);
        return view($this->theme_root . '/groups');
    }
}