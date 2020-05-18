<?php /** @noinspection PhpUndefinedFieldInspection */

/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/8/4
 * Time: 下午6:33
 */
namespace app\user\controller;

use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\extra\controller\BaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\response\View;

class Info extends BaseController {

    /**
     * 用户信息(Public)
     * @param $user
     * @return View
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function index($user) {
        $user = UserModel::get(['user_id' => $user]);
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
