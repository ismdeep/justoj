<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 7:45 PM
 */

namespace app\groups\controller;


use app\api\model\GroupJoinModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Myjoin extends UserBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'groups');
    }

    /**
     * /groups/myjoin
     *
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function index() {
        if (!$this->loginuser) $this->redirect('/login?redirect=%2Fgroups%2Fmyjoin');
        $group_joins = (new GroupJoinModel())
            ->where([
                'user_id' => $this->loginuser->user_id,
                'deleted' => 0
            ])
            ->order('id', 'desc')
            ->paginate(10);
        $this->assign('group_joins', $group_joins);
        return view($this->theme_root . '/groups-my-join');
    }
}
