<?php


namespace app\home\controller;


use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\home\common\HomeBaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\response\View;

class User extends HomeBaseController {

    /**
     * 用户信息(Public)
     *
     * @param $user_id
     * @return View
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function get_user_detail($user_id) {
        $user = UserModel::get(['user_id' => $user_id]);
        $rank = (new UserModel)->where('solved', '>', $user['solved'])->count() + 1;
        $ac_solutions = (new SolutionModel)
            ->distinct(true)
            ->field('problem_id')
            ->where('user_id', $user->user_id)
            ->where('result', 4)
            ->order('problem_id', 'asc')
            ->select();

        $this->assign('user', $user);
        $this->assign('rank', $rank);
        $this->assign('ac_list', $ac_solutions);

        return view($this->theme_root . '/user-info');
    }

}