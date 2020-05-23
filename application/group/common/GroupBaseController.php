<?php


namespace app\group\common;


use app\api\model\GroupModel;
use app\extra\controller\UserBaseController;
use think\Request;

class GroupBaseController extends UserBaseController {

    /* @var $group GroupModel */
    public $group;

    public function __construct(Request $request = null, $id) {
        parent::__construct($request);
        // 判断是否登录，如果没有登录直接跳转到登录页面
        if (!$this->loginuser) $this->redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));

        // 获取group信息
        $this->group = GroupModel::get(['id' => $id]);
        intercept($this->group == null, 'NOT EXISTS');
        intercept($this->group->deleted == 1, 'DELETED');
        $this->assign('group', $this->group);
    }

}