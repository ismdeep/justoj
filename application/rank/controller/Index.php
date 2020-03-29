<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 22:51
 */

namespace app\rank\controller;

use app\api\model\UserModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Index extends UserBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'rank');
    }

    /**
     * Rank home page
     *
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function index() {
        $users = (new UserModel)->order('solved', 'desc')->paginate(100);
        $this->assign('users', $users);
        $this->assign('cur_rank', 1 + (($users->currentPage() - 1) * 100));
        return view($this->theme_root . '/rank');
    }
}
