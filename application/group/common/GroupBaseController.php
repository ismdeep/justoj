<?php


namespace app\group\common;


use app\api\model\GroupModel;
use app\extra\controller\UserBaseController;
use think\Request;

class GroupBaseController extends UserBaseController {

    /* @var $group GroupModel */
    public $group;
    /* @var $is_group_manager boolean */
    public $is_group_manager;

    /**
     * GroupBaseController constructor.
     *
     * @param Request|null $request
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct(Request $request = null, $id = '') {
        parent::__construct($request);
        // 判断是否登录，如果没有登录直接跳转到登录页面
        if (!$this->login_user) $this->redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));

        /* 获取group信息 */
        intercept($id == '', 'Error on [id=\'\']');
        $this->group = GroupModel::get(['id' => $id]);
        intercept($this->group == null, 'NOT EXISTS');
        intercept($this->group->deleted == 1, 'DELETED');
        $this->assign('group', $this->group);

        /* 判断当前用户是否为Group管理员 */
        $this->is_group_manager = false;
        if ($this->login_user && $this->group->owner_id == $this->login_user->user_id) {
            $this->is_group_manager = true;
        }
        $this->assign('is_group_manager', $this->is_group_manager);
    }

}